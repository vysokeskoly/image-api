<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Entity;

class Status
{
    private string $status;
    private bool $isSuccess;
    private int $statusCode;
    private array $messages;

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
