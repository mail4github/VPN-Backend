<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Concern\JsonResponseTrait;
use App\Entity\User;
use App\Request\EnableTwoFactorAuthRequest;
use App\Security\RecoveryCodeManager;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Builder\BuilderInterface;
use OpenApi\Attributes as OA;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TwoFactorUpdateController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly ManagerRegistry $doctrine,
        private readonly RecoveryCodeManager $recoveryCodeManager,
        private readonly BuilderInterface $qrBuilder
    ) {}

    #[Route('/api/2fa/enable', name: 'app_two_factor_enable', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/2fa/enable',
        summary: 'Checks code with Google Authenticator and enables 2FA for user on success',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['_auth_code'],
                    properties: [
                        new OA\Property(
                            property: '_auth_code',
                            description: 'Google Authenticator code',
                            type: 'string',
                            example: '789123'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: '2FA enabled'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid code'
            ),
        ]
    )]
    public function enable(
        #[CurrentUser]
        ?User $user,
        #[MapRequestPayload]
        EnableTwoFactorAuthRequest $request
    ): Response {
        if (null === $user) {
            return $this->unauthorized();
        }

        $verified = $this->googleAuthenticator->checkCode($user, $request->_auth_code);
        if (!$verified) {
            return $this->error('Invalid TOTP code');
        }

        $user->setIsTwoFactorAuthEnabled(true);

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return $this->json($user);
    }

    #[Route('api/2fa/disable', name: 'app_two_factor_disable', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/2fa/disable',
        summary: 'Disables 2FA for user',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Settings updated'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized action'
            ),
        ]
    )]
    public function disable(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->unauthorized();
        }

        $user->setIsTwoFactorAuthEnabled(false);
        $user->setGoogleAuthenticatorSecret(null);
        foreach ($user->getRecoveryCodes() as $code) {
            $user->removeRecoveryCode($code);
        }

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        return $this->json($user);
    }

    #[Route('/api/2fa/configure', name: 'app_two_factor_configure', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/2fa/configure',
        summary: 'Prepares user to interact with 2FA',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 401,
                description: 'Authentication required'
            ),
            new OA\Response(
                response: 200,
                description: 'Codes issued'
            ),
        ]
    )]
    public function configure(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->unauthorized();
        }

        if ($user->hasTwoFactorConfiguration()) {
            throw new UnprocessableEntityHttpException('2FA is already configured');
        }

        $totpSecret = $this->googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($totpSecret);
        $this->recoveryCodeManager->issueRecoveryCodesForUser($user);

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        $qrContents = $this->googleAuthenticator->getQRContent($user);
        $qrCode = $this->qrBuilder
            ->create()
            ->data($qrContents)
            ->size(400)
            ->build()
            ->getString();

        return $this->json([
            'qr' => base64_encode($qrCode),
            'secret' => $totpSecret,
            'codes' => $user->getRecoveryCodes(),
        ]);
    }
}
