<?php

declare(strict_types=1);

namespace App\Security\Token\Payload;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserPayloadGeneratorInterface
{
    /**
     * @return array{
     *     iss: string,
     *     exp: int,
     *     iat: int,
     *     roles: string[],
     *     id: string
     * }
     */
    public function create(UserInterface $user, bool $is2FaEnabled): array;
}
