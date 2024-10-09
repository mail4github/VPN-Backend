<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Token\AuthenticationTokenUserExtractorInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class JWTAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly AuthenticationTokenUserExtractorInterface $authenticationTokenUserExtractor,
        private readonly UserLoaderInterface $userLoader,
        private readonly string $authorizationHeaderName,
    ) {}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has($this->authorizationHeaderName);
    }

    public function authenticate(Request $request): Passport
    {
        $tokenValue = (string) $request->headers->get($this->authorizationHeaderName);
        $token = explode(' ', $tokenValue);
        if (
            2 !== \count($token)
            || 'Bearer' !== $token[0]
        ) {
            throw new AuthenticationException('Invalid token value.');
        }

        $jwt = $token[1];
        try {
            $tokenPayload = $this->authenticationTokenUserExtractor->extract($jwt);
        } catch (\Throwable $exception) {
            throw new AuthenticationException('Invalid token: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $userLoaderCallback = fn (string $id, array $roles) => ($this->userLoader)($id, $roles);

        return new SelfValidatingPassport(
            new UserBadge(
                $tokenPayload->getUserIdentifier(),
                $userLoaderCallback,
                $tokenPayload->getRoles(),
            ),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($token instanceof TwoFactorTokenInterface) {
            $token->setTwoFactorProviderPrepared('google');
        }

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }
}
