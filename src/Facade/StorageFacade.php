<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Facade;

use Assert\Assertion;
use Symfony\Component\Filesystem\Filesystem;
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

    private string $storagePath;
    private NamespaceService $namespaceService;
    private Filesystem $fileSystem;
    private Status $status;

    public function __construct(string $storagePath, NamespaceService $namespaceService, Filesystem $fileSystem)
    {
        $this->storagePath = $storagePath;
        $this->fileSystem = $fileSystem;
        $this->namespaceService = $namespaceService;
    }

    public function saveFiles(FileBag $files): void
    {
        if (!$files->count()) {
            $this->status = new Status('NO_FILES', false, 500);

            return;
        }

        $uploadedFiles = array_filter($files->all(), function ($file) {
            return $file instanceof UploadedFile;
        });

        /** @var UploadedFile $uploadedFile */
        foreach ($uploadedFiles as $uploadedFile) {
            try {
                $fileName = $uploadedFile->getClientOriginalName();
                $file = $uploadedFile->move($this->getStoragePath(), $fileName);

                Assertion::file((string) $file->getRealPath());
                Assertion::same($fileName, $file->getFilename());
                $this->createSuccessStatus($fileName);
            } catch (\Throwable $e) {
                $this->createErrorStatus($e);
            }
        }
    }

    private function getStoragePath(): string
    {
        return sprintf('%s/%s/', rtrim($this->storagePath, '/'), $this->namespaceService->getNamespace());
    }

    private function createSuccessStatus(string $fileName): void
    {
        $this->status = new Status(self::STATUS_OK, true, 200, [$fileName]);
    }

    private function createErrorStatus(\Throwable $e, int $statusCode = 500): void
    {
        $this->status = new Status(self::STATUS_ERROR, false, $statusCode, [get_class($e), $e->getMessage()]);
    }

    public function delete(string $fileName): void
    {
        try {
            $filePath = $this->getStoragePath() . $fileName;
            if (!$this->fileSystem->exists($filePath)) {
                throw $this->createNotFoundException($fileName);
            }

            $this->fileSystem->remove($filePath);

            $this->createSuccessStatus($fileName);
        } catch (NotFoundHttpException $e) {
            $this->createErrorStatus($e, 404);
        } catch (\Throwable $e) {
            $this->createErrorStatus($e);
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
        return array_values(
            array_map(
                function (SplFileInfo $file) {
                    return $file->getFilename();
                },
                iterator_to_array((new Finder())->files()->in($this->getStoragePath())->depth('== 0'))
            )
        );
    }

    public function getImage(string $fileName): ?string
    {
        $filePath = $this->getStoragePath() . $fileName;
        $exists = $this->fileSystem->exists($filePath);

        $exists
            ? $this->createSuccessStatus($fileName)
            : $this->createErrorStatus($this->createNotFoundException($fileName), 404);

        return $exists
            ? (string) file_get_contents($filePath)
            : null;
    }
}
