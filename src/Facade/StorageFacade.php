<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Facade;

use Assert\Assertion;
use MF\Collection\Immutable\Generic\Seq;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VysokeSkoly\ImageApi\Entity\Status;
use VysokeSkoly\ImageApi\Service\NamespaceService;

class StorageFacade
{
    private const STATUS_OK = 'OK';
    private const STATUS_ERROR = 'ERROR';

    private ?Status $status = null;

    public function __construct(private string $storagePath, private NamespaceService $namespaceService, private Filesystem $fileSystem)
    {
    }

    public function saveFiles(FileBag $files): void
    {
        if (!$files->count()) {
            $this->status = new Status('NO_FILES', false, 500);

            return;
        }

        $this->status = Seq::from($files->all())
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->reduce(function (?Status $status, UploadedFile $uploadedFile) {
                if (!$status || $status->isSuccess()) {
                    try {
                        $fileName = $uploadedFile->getClientOriginalName();
                        if (!$status) {
                            $status = $this->createSuccessStatus();
                        }
                        $file = $uploadedFile->move($this->getStoragePath(), $fileName);

                        Assertion::file((string) $file->getRealPath());
                        Assertion::same($fileName, $file->getFilename());

                        $status->addMessage($fileName);
                    } catch (\Throwable $e) {
                        return $this->createErrorStatus($e);
                    }
                }

                return $status;
            });
    }

    private function getStoragePath(): string
    {
        return sprintf('%s/%s/', rtrim($this->storagePath, '/'), $this->namespaceService->getNamespace());
    }

    private function createSuccessStatus(?string $fileName = null): Status
    {
        return new Status(self::STATUS_OK, true, 200, $fileName ? [$fileName] : []);
    }

    private function createErrorStatus(\Throwable $e, int $statusCode = 500): Status
    {
        return new Status(self::STATUS_ERROR, false, $statusCode, [get_class($e), $e->getMessage()]);
    }

    public function delete(string $fileName): void
    {
        try {
            $filePath = $this->getStoragePath() . $fileName;
            if (!$this->fileSystem->exists($filePath)) {
                throw $this->createNotFoundException($fileName);
            }

            $this->fileSystem->remove($filePath);

            $this->status = $this->createSuccessStatus($fileName);
        } catch (NotFoundHttpException $e) {
            $this->status = $this->createErrorStatus($e, 404);
        } catch (\Throwable $e) {
            $this->status = $this->createErrorStatus($e);
        }
    }

    private function createNotFoundException(string $fileName): \Throwable
    {
        return new NotFoundHttpException(sprintf("File '%s' was not found.", $fileName));
    }

    public function getStatus(): Status
    {
        return $this->status ?? new Status('unknown', false, 500);
    }

    public function listAll(): array
    {
        try {
            return array_values(
                array_map(
                    fn (SplFileInfo $file) => $file->getFilename(),
                    iterator_to_array((new Finder())->files()->in($this->getStoragePath())->depth('== 0')),
                ),
            );
        } catch (DirectoryNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    public function getImage(string $fileName): ?string
    {
        $filePath = $this->getStoragePath() . $fileName;
        $exists = $this->fileSystem->exists($filePath);

        $this->status = $exists
            ? $this->createSuccessStatus($fileName)
            : $this->createErrorStatus($this->createNotFoundException($fileName), 404);

        return $exists
            ? (string) file_get_contents($filePath)
            : null;
    }
}
