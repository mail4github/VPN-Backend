<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\NewsletterHtmlDto;
use App\Dto\NewsletterTextDto;
use App\Service\NewsletterService;
use App\Service\TemplateService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        protected NewsletterService $newsletterService,
        protected TemplateService $storage,
    ) {}

    #[Route('/api/newsletter/template', name: 'newsletter_send_template', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/newsletter/template',
        summary: 'Send mail with twig/html templates with parameters',
        tags: ['Newsletter'],
        parameters: [
            new OA\Parameter(
                name: 'templateName',
                description: 'Name of existing twig/html template',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'newsletter-template1')
            ),
            new OA\Parameter(
                name: 'subject',
                description: 'Mail subject',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'Discount alert!')
            ),
            new OA\Parameter(
                name: 'recipient',
                description: 'Mail recepient',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'test@test.ru')
            ),
            new OA\Parameter(
                name: 'params',
                description: 'Twig parameters',
                in: 'query',
                schema: new OA\Schema(
                    type: 'object',
                    example: ['username' => 'Cesar', 'percent' => '50%', 'date' => 'today']
                )
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 500, description: 'Transport error'),
        ]
    )]
    public function sendTemplate(#[MapRequestPayload] NewsletterHtmlDto $dto): Response
    {
        $dto->templateName = $this->storage->realName($dto->templateName);
        $this->newsletterService->sendTemplate($dto);

        return $this->json([
            'message' => 'send',
        ]);
    }

    #[Route('/api/newsletter/text', name: 'newsletter_send_text', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/newsletter/text',
        summary: 'Send mail with text',
        tags: ['Newsletter'],
        parameters: [
            new OA\Parameter(
                name: 'subject',
                description: 'Mail subject',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'Discount alert!')
            ),
            new OA\Parameter(
                name: 'recipient',
                description: 'Mail recepient',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'test@test.ru')
            ),
            new OA\Parameter(
                name: 'text',
                description: 'Mail content',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'content')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 500, description: 'Transport error'),
        ]
    )]
    public function sendText(#[MapRequestPayload] NewsletterTextDto $dto): Response
    {
        $this->newsletterService->sendText($dto);

        return $this->json([
            'message' => 'send',
        ]);
    }
}
