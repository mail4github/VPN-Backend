<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\TemplateDto;
use App\Service\TemplateService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        protected TemplateService $storage
    ) {}

    #[Route('/api/template/{name}', name: 'view_template', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/template/{name}',
        summary: 'View template content without parameters',
        tags: ['Twig-templates'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: 'Template name',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'newsletter-template1')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'html', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Template not found'),
            new OA\Response(response: 422, description: 'Unprocessable Content'),
            new OA\Response(response: 423, description: 'Template file is locked'),
            new OA\Response(response: 500, description: 'Write or read to templates dir is not allowed'),
        ]
    )]
    public function view(string $name): Response
    {
        $realName = $this->storage->realName($name);

        return $this->json([
            'name' => $name,
            'html' => $this->storage->read($realName),
        ]);
    }

    #[Route('/api/template', name: 'store_template', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/template',
        summary: 'Create new twig/html template',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'newsletter-template1'),
                    new OA\Property(property: 'html', type: 'string', example: '<div>Hello {{ username }}, we have a special {{ percent }}% discount at {{ date }}!</div>'),
                ],
                type: 'object'
            )
        ),
        tags: ['Twig-templates'],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 422, description: 'Unprocessable Content'),
            new OA\Response(response: 423, description: 'Template file is locked'),
            new OA\Response(response: 500, description: 'Write or read to templates upload dir is not allowed'),
        ]
    )]
    public function store(#[MapRequestPayload] TemplateDto $dto): Response
    {
        $realName = $this->storage->realName($dto->name);
        $this->storage->replace($realName, $dto->html);

        return $this->json([
            'name' => $dto->name,
            'html' => $this->storage->read($realName),
        ]);
    }

    #[Route('/api/template', name: 'update_template', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(
        path: '/api/template',
        summary: 'Update existing twig/html template',
        tags: ['Twig-templates'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: "Template name in format '<scope>-<name>'. <scope> - template folder, <name> - template name",
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'newsletter-template1')
            ),
            new OA\Parameter(
                name: 'html',
                description: 'Template content in html/twig format',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: '<div>Hello {{ username }}, we have a special {{ percent }}% discount at {{ date }}!</div>')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 404, description: 'Template not found'),
            new OA\Response(response: 422, description: 'Unprocessable Content'),
            new OA\Response(response: 423, description: 'Template file is locked'),
            new OA\Response(response: 500, description: 'Write or read to templates upload dir is not allowed'),
        ]
    )]
    public function update(#[MapRequestPayload] TemplateDto $dto): Response
    {
        $realName = $this->storage->realName($dto->name);
        $this->storage->replace($realName, $dto->html);

        return $this->json([
            'name' => $dto->name,
            'html' => $this->storage->read($realName),
        ]);
    }

    #[Route('/api/template/{name}', name: 'delete_template', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/template/{name}',
        summary: 'Delete template',
        tags: ['Twig-templates'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: 'Template name',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'newsletter-template1')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 404, description: 'Template not found'),
            new OA\Response(response: 422, description: 'Unprocessable Content'),
            new OA\Response(response: 423, description: 'Template file is locked'),
            new OA\Response(response: 500, description: 'Write or read to templates dir is not allowed'),
        ]
    )]
    public function delete(string $name): Response
    {
        $this->storage->delete($this->storage->realName($name));

        return $this->json([
            'name' => $name,
        ]);
    }
}
