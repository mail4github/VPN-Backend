<?php

declare(strict_types=1);

namespace App\Security\Token\Payload;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserPayloadGenerator implements UserPayloadGeneratorInterface
{
    public function __construct(
        private int $expiresDefault = 86400,
        private int $expires2fa = 300,
        private string $issuer = 'nodus.vpn',
    ) {}

    public function create(UserInterface $user, bool $is2FaEnabled = false): array
    {
        $roles = $user->getRoles();
        if ($is2FaEnabled) {
            $roles = ['IS_AUTHENTICATED_2FA_IN_PROGRESS'];
        } else {
            $key = array_search('IS_AUTHENTICATED_2FA_IN_PROGRESS', $roles);
            if (false !== $key) {
                unset($roles[$key]);
                $roles = array_values($roles);
            }
        }

        $timeNow = time();
        $expiresIn = $timeNow + ($is2FaEnabled ? $this->expires2fa : $this->expiresDefault);

        return [
            'iss' => $this->issuer,
            'id' => $user->getUserIdentifier(),
            'roles' => $roles,
            'exp' => $expiresIn,
            'iat' => $timeNow,
        ];
    }
}
