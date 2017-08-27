<?php

namespace VysokeSkoly\ImageApi\Facade;

use Assert\Assertion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VysokeSkoly\ImageApi\Entity\Status;

class StorageFacade
{
    /** @var string */
    private $storagePath;

    /** @var Filesystem */
    private $fileSystem;

    /** @var Status */
    private $status;

    public function __construct(string $storagePath, Filesystem $fileSystem)
    {
        $this->storagePath = $storagePath;
        $this->fileSystem = $fileSystem;
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
                $file = $uploadedFile->move($this->storagePath, $fileName);

                Assertion::file($file->getRealPath());
                Assertion::same($fileName, $file->getFilename());
                $this->createSuccessStatus($fileName);
            } catch (\Exception $e) {
                $this->createErrorStatus($e);
            }
        }
    }

    private function createSuccessStatus(string $fileName): void
    {
        $this->status = new Status('OK', true, 200, [$fileName]);
    }

    private function createErrorStatus(\Throwable $e, int $statusCode = 500): void
    {
        $this->status = new Status('ERROR', false, $statusCode, [get_class($e), $e->getMessage()]);
    }

    public function delete(string $fileName): void
    {
        try {
            $filePath = $this->storagePath . $fileName;
            if (!$this->fileSystem->exists($filePath)) {
                throw new NotFoundHttpException(sprintf("File '%s' was not found.", $fileName));
            }

            $this->fileSystem->remove($filePath);

            $this->createSuccessStatus($fileName);
        } catch (NotFoundHttpException $e) {
            $this->createErrorStatus($e, 404);
        } catch (\Exception $e) {
            $this->createErrorStatus($e);
        }
    }

    public function getStatus(): Status
    {
        return $this->status ?? new Status('unknown', false, 500);
    }
}
