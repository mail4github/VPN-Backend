<?php

declare(strict_types=1);

namespace App\Security\Token;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;

interface AuthenticationTokenUserExtractorInterface
{
    /**
     * @throws AuthenticationException
     * @throws AuthenticationExpiredException
     */
    public function extract(string $token): AuthenticatedUserToken;
}
