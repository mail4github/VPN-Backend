<?php

declare(strict_types=1);

namespace App\Security;

use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorAuthenticationRequiredHandler implements AuthenticationRequiredHandlerInterface
{
    public function onAuthenticationRequired(Request $request, TokenInterface $token): Response
    {
        // Return the response to tell the client that authentication hasn't completed yet and
        // two-factor authentication is required.
        return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }
}
