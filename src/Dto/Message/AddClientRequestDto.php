<?php

declare(strict_types=1);

namespace App\Dto\Message;

readonly class AddClientRequestDto
{
    public function __construct(
        private int $serverId,
        private string $clientName,
        private string $config
    ) {}

    public function getServerId(): int
    {
        return $this->serverId;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getConfig(): string
    {
        return $this->config;
    }
}
