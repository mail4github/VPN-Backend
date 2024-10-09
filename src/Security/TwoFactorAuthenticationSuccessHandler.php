<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Token\AuthenticationTokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class TwoFactorAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private AuthenticationTokenGeneratorInterface $authenticationTokenGenerator,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if (null === ($user = $token->getUser())) {
            throw new AuthenticationException();
        }

        return new JsonResponse([
            'token' => $this->authenticationTokenGenerator->generate($user, false),
        ]);
    }
}
