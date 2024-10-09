<?php

declare(strict_types=1);

namespace App\Security\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final readonly class AuthenticationJWTTokenUserExtractor implements AuthenticationTokenUserExtractorInterface
{
    public function __construct(
        private string $publicKey,
        private string $algorithm
    ) {}

    public function extract(string $token): AuthenticatedUserToken
    {
        try {
            $decoded = (array) JWT::decode($token, new Key($this->publicKey, $this->algorithm));
        } catch (\UnexpectedValueException $exception) {
            throw new AuthenticationException('Invalid JWT token', 0, $exception);
        }

        $id = $decoded['id'] ?? '';
        $roles = $decoded['roles'] ?? '';
        if (!$id || !$roles) {
            throw new \InvalidArgumentException(sprintf('Invalid JWT token `%s`', $token));
        }

        return new AuthenticatedUserToken($id, $roles);
    }
}
