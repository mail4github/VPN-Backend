<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Endroid\QrCode\Builder\Builder;
use OpenApi\Attributes as OA;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TwoFactorQrCodeController extends AbstractController
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly Builder $builder,
    ) {}

    #[Route('/api/2fa_qr', name: 'app_two_factor_qr_code', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/2fa_qr',
        summary: 'Generated QR code',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Generated QR code',
                content: new OA\MediaType(
                    mediaType: 'image/png',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: '2FA is not enabled'
            ),
            new OA\Response(
                response: 401,
                description: 'User is not authenticated'
            ),
        ]
    )]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'error' => 'Authentication required to access this resource',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (null === $user->getGoogleAuthenticatorSecret()) {
            return $this->json([
                'error' => 'User has not enabled 2FA authentication',
            ], Response::HTTP_BAD_REQUEST);
        }

        $qrContents = $this->googleAuthenticator->getQRContent($user);
        $qrCode = $this->builder
            ->create()
            ->data($qrContents)
            ->size(400)
            ->build()
            ->getString();

        return new Response(
            content: $qrCode,
            status: 200,
            headers: ['Content-Type' => 'image/png']
        );
    }
}
