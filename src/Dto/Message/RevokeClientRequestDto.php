<?php

declare(strict_types=1);

namespace App\Dto\Message;

readonly class RevokeClientRequestDto
{
    public function __construct(
        private int $serverId,
        private string $clientName,
        private bool $status
    ) {}

    public function getServerId(): int
    {
        return $this->serverId;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }
}
