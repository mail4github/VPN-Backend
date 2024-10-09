<?php

declare(strict_types=1);

namespace App\Security\Token;

use App\Security\Token\Payload\UserPayloadGeneratorInterface;
use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthenticationJWTTokenGenerator implements AuthenticationTokenGeneratorInterface
{
    public function __construct(
        private UserPayloadGeneratorInterface $userPayloadGenerator,
        private string $privateKey,
        private string $algorithm = 'RS256',
        private string $passShare = ''
    ) {}

    public function generate(UserInterface $user, bool $is2FaEnabled): string
    {
        $privateKey = openssl_pkey_get_private($this->privateKey, $this->passShare);
        if (!$privateKey) {
            throw new \RuntimeException(sprintf('Can not restore execute openssl_pkey_get_private from `%s`', $this->privateKey));
        }

        $payload = $this->userPayloadGenerator->create($user, $is2FaEnabled);

        return JWT::encode($payload, $privateKey, $this->algorithm);
    }
}
