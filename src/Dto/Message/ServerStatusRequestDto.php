<?php

declare(strict_types=1);

namespace App\Dto\Message;

readonly class ServerStatusRequestDto
{
    public function __construct(
        private int $serverId,
        private bool $status
    ) {}

    public function getServerId(): int
    {
        return $this->serverId;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }
}
