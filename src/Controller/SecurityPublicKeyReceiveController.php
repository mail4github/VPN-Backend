<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

final class SecurityPublicKeyReceiveController extends AbstractController
{
    #[Route('/api/id_rsa.pub', name: 'app_public_key', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        $publicKey = $this->getParameter('security_token_key_public');
        if (!\is_string($publicKey)) {
            throw new HttpException(500, 'Wrong configuration.');
        }

        return new Response($publicKey);
    }
}
