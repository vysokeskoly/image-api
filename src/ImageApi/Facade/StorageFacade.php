<?php

namespace VysokeSkoly\ImageApi\Facade;

use Assert\Assertion;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use VysokeSkoly\ImageApi\Entity\Status;

class StorageFacade
{
    /** @var string */
    private $storagePath;

    /** @var Status */
    private $status;

    public function __construct(string $storagePath)
    {
        $this->storagePath = $storagePath;
    }

    public function saveFiles(FileBag $files): void
    {
        if (!$files->count()) {
            $this->status = new Status('NO_FILES', false);

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
                $this->status = new Status('OK', true, [$fileName]);
            } catch (\Exception $e) {
                $this->status = new Status('ERROR', false, [get_class($e), $e->getMessage()]);
            }
        }
    }

    public function getStatus(): Status
    {
        return $this->status ?? new Status('unknown', false);
    }
}
