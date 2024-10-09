<?php

declare(strict_types=1);

namespace App\Security\Token;

use Symfony\Component\Security\Core\User\UserInterface;

interface AuthenticationTokenGeneratorInterface
{
    public function generate(UserInterface $user, bool $is2FaEnabled): string;
}
