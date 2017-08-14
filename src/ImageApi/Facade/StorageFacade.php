<?php

namespace VysokeSkoly\ImageApi\Facade;

use Symfony\Component\HttpFoundation\FileBag;

class StorageFacade
{
    /** @var string */
    private $status = '';

    public function saveImage(FileBag $files): void
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
