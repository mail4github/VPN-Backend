<?php

declare(strict_types=1);

namespace App\Dto\Message;

readonly class VpnServerDto
{
    public function __construct(
        private int $id,
        private string $ip,
        private string $username,
        private string $password,
        private string $protocol,
        private int $port = 22
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}
