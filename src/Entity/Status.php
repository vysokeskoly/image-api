<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Entity;

class Status
{
    public function __construct(private string $status, private bool $isSuccess, private int $statusCode, private array $messages = [])
    {
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function addMessage(string|array|\JsonSerializable $message): void
    {
        $this->messages[] = $message;
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
