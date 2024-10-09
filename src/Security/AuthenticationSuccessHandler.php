<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Token\AuthenticationTokenGeneratorInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private AuthenticationTokenGeneratorInterface $authenticationTokenGenerator
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user) {
            throw new \RuntimeException('User authenticated, but can not be extracted from token.');
        }

        $twoFactorEnabledStatus = $user->getIsTwoFactorAuthEnabled();
        if ($token instanceof TwoFactorTokenInterface) {
            $token = $this->authenticationTokenGenerator
                ->generate($user, true);

            return $this->createResponse($token);
        }

        return $this->createResponse(
            $this->authenticationTokenGenerator->generate($user, $twoFactorEnabledStatus)
        );
    }

    private function createResponse(string $token): Response
    {
        return new JsonResponse([
            'token' => $token,
        ]);
    }
}
