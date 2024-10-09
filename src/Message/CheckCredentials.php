<?php

declare(strict_types=1);

namespace App\Message;

use App\Dto\Message\VpnServerDto;

readonly class CheckCredentials
{
    public function __construct(
        private VpnServerDto $vpnServer
    ) {}

    public function getVpnServer(): VpnServerDto
    {
        return $this->vpnServer;
    }
}
