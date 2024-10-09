<?php

declare(strict_types=1);

namespace App\Message;

use App\Dto\Message\VpnServerDto;

readonly class RevokeClient
{
    public function __construct(
        private VpnServerDto $vpnServer,
        private string $clientName
    ) {}

    public function getVpnServer(): VpnServerDto
    {
        return $this->vpnServer;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }
}
