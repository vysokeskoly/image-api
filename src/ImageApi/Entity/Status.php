<?php

namespace VysokeSkoly\ImageApi\Entity;

class Status
{
    /** @var string */
    private $status;

    /** @var bool */
    private $isSuccess;

    /** @var int */
    private $statusCode;

    /** @var array */
    private $messages;

    public function __construct(string $status, bool $isSuccess, int $statusCode, array $messages = [])
    {
        $this->status = $status;
        $this->isSuccess = $isSuccess;
        $this->statusCode = $statusCode;
        $this->messages = $messages;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
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
