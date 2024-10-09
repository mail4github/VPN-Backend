<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiLogoutController extends AbstractController
{
    #[Route('/api/logout', name: 'api_logout', methods: ['GET'])]
    #[OA\Get(
        path: '/api/logout',
        summary: 'Logout from application',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: 'Successful logout'),
            new OA\Response(response: '302', description: 'Successful logout'),
        ]
    )]
    public function logout(): void
    {
        throw new \Exception('Authenticator should take it from here automatically');
    }
}
