<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Message\AddClientRequestDto;
use App\Dto\Message\RevokeClientRequestDto;
use App\Dto\Message\ServerStatusRequestDto;
use App\Repository\VpnConnectionRepository;
use App\Repository\VpnServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/provider/notification', name: 'provider_notification_')]
class ProviderNotificationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    #[
        Route('/deploy', name: 'deploy', methods: ['POST']),
        OA\Post(
            path: '/api/provider/notification/deploy',
            summary: 'Internal API endpoint responsible for notification from the provider about the completion of the deployment task',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['serverId', 'status'],
                    properties: [
                        new OA\Property(property: 'serverId', type: 'int', example: 123),
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                    ]
                )
            ),
            tags: ['Provider Notification']
        )
    ]
    public function deployNotification(
        #[MapRequestPayload]
        ServerStatusRequestDto $dto,
        VpnServerRepository $serverRepository
    ): JsonResponse {
        if ($dto->getStatus()) {
            $server = $serverRepository->find($dto->getServerId());

            if (null === $server) {
                throw $this->createNotFoundException(sprintf('VPN Server with id %s not found', $dto->getServerId()));
            }

            $server->setIsReadyToUse(true);

            $this->em->persist($server);
            $this->em->flush();
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[
        Route('/add-client', name: 'add_client', methods: ['POST']),
        OA\Post(
            path: '/api/provider/notification/add-client',
            summary: 'Internal API endpoint responsible for notification from the provider about the completion of creating new client task',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['serverId', 'status'],
                    properties: [
                        new OA\Property(property: 'serverId', type: 'int', example: 123),
                        new OA\Property(property: 'clientName', type: 'string', example: 'user-123'),
                        new OA\Property(property: 'config', type: 'string', example: 'ZW5jb2RlZCBzdHJpbmc='),
                    ]
                )
            ),
            tags: ['Provider Notification']
        )
    ]
    public function addClientNotification(
        #[MapRequestPayload]
        AddClientRequestDto $dto,
        VpnConnectionRepository $connectionRepository
    ): JsonResponse {
        $client = $connectionRepository->findByClientName($dto->getClientName());

        if (null === $client) {
            return $this->json(['message' => 'Client not found', 'errorCode' => 404], Response::HTTP_NOT_FOUND);
        }

        $client->setClientConfig($dto->getConfig());
        $this->em->persist($client);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[
        Route('/revoke-client', name: 'revoke_client', methods: ['POST']),
        OA\Post(
            path: '/api/provider/notification/revoke-client',
            summary: 'Internal API endpoint responsible for notification from the provider about the completion of creating new client task',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['serverId', 'status'],
                    properties: [
                        new OA\Property(property: 'serverId', type: 'int', example: 123),
                        new OA\Property(property: 'clientName', type: 'string', example: 'user-123'),
                        new OA\Property(property: 'status', type: 'bool', example: true),
                    ]
                )
            ),
            tags: ['Provider Notification']
        )
    ]
    public function revokeClientNotification(
        #[MapRequestPayload]
        RevokeClientRequestDto $dto
    ): JsonResponse {
        if (false === $dto->getStatus()) {
            // TODO: Do some logic here if client wasn't revoked.
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[
        Route('/credentials', name: 'credentials', methods: ['POST']),
        OA\Post(
            path: '/api/provider/notification/credentials',
            summary: 'Internal API endpoint responsible for notification from the provider about the completion of check credentials task',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['serverId', 'status'],
                    properties: [
                        new OA\Property(property: 'serverId', type: 'int', example: 123),
                        new OA\Property(property: 'status', type: 'bool', example: true),
                    ]
                )
            ),
            tags: ['Provider Notification']
        )
    ]
    public function checkCredentialsNotification(
        #[MapRequestPayload]
        ServerStatusRequestDto $dto
    ): JsonResponse {
        if (false === $dto->getStatus()) {
            // TODO: Do some logic here if credentials wrong.
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
