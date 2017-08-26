<?php

namespace VysokeSkoly\ImageApi\Entity;

class Status
{
    /** @var string */
    private $status;

    /** @var bool */
    private $isSuccess;

    /** @var array */
    private $messages;

    public function __construct(string $status, bool $isSuccess, array $messages = [])
    {
        $this->status = $status;
        $this->isSuccess = $isSuccess;
        $this->messages = $messages;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'isSuccess' => $this->isSuccess,
            'messages' => $this->messages,
        ];
    }
}
