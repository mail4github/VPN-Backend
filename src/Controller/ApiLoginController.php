<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Security\Token\AuthenticationTokenGeneratorInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationTokenGeneratorInterface $authenticationTokenGenerator
    ) {}

    #[Route('/api/login', name: 'api_login', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'username'),
                    new OA\Property(property: 'password', type: 'string', example: 'nmc94358nd'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: 'Success message or required 2FA message'),
            new OA\Response(response: '400', description: 'Bad credentials'),
        ]
    )]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json([
            'token' => $this->authenticationTokenGenerator->generate($user, $user->getIsTwoFactorAuthEnabled()),
        ]);
    }
}
