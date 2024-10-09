<?php

declare(strict_types=1);

namespace App\Security\Token;

final readonly class AuthenticatedUserToken
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $id,
        private array $roles
    ) {}

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}
