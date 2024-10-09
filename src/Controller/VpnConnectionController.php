<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Message\VpnServerDto;
use App\Dto\VpnConnectionAddDto;
use App\Dto\VpnConnectionsGetDto;
use App\Entity\VpnServer;
use App\Message\AddClient;
use App\Repository\VpnConnectionRepository;
use App\Repository\VpnServerRepository;
use App\Service\UsefulToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VpnConnectionController.
 */
class VpnConnectionController extends AbstractController
{
    public function __construct(
        private VpnConnectionRepository $vpnConnectionRepository
    ) {}

    /**
     * Retrieves a list of connections to VPN servers.
     *
     * @Route("/api/vpn_connections", name="vpn_connections", methods={"GET"})
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param EntityManagerInterface $entityManager
     * @param VpnConnectionsGetDto   $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/vpn_connections', name: 'vpn_connections', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/vpn_connections',
        summary: 'Retrieves a list of connections to VPN servers.',
        tags: ['VPN Connections'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort connections',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'created',
                    enum: ['created', 'ip', 'country']
                )
            ),
            new OA\Parameter(
                name: 'sort_order',
                description: 'Sort order. Ascend or descend',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'desc',
                    enum: ['asc', 'desc']
                )
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Retrieve list of connections which have user_id name like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
            new OA\Parameter(
                name: 'offset',
                description: 'Skip the first `offset` rows',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 0
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Return the number of values not exceeding that specified in the parameter:`limit`',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 24
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A list of connections',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'user_id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'ip',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'country',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'created',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'modified',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'server_id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'duration',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'description',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'protocol',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'total_traffic',
                                type: 'number'
                            ),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function vpnConnections(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        VpnConnectionsGetDto $dto
    ): JsonResponse {
        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from('app.public."vpn_connection"', 'cncts')
            ->setFirstResult($dto->offset)
            ->setMaxResults($dto->limit)
        ;

        if (!empty($dto->user_id)) {
            $queryBuilder->where('cncts.user_id = :user_id')->setParameter('user_id', $dto->user_id);
        }

        $queryBuilder->orderBy('cncts.'.$usefulToolsHelper->sanitizeString($dto->sort_by), $dto->sort_order);

        $vpnConnections = $queryBuilder->execute()->fetchAll();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($vpnConnections);
    }

    /**
     * Add new VPN Connection.
     *
     * @Route("/api/vpn_connection", name="add_vpn_connection", methods={"POST"})
     *
     * @param UsefulToolsHelper   $usefulToolsHelper
     * @param VpnConnectionAddDto $dto
     * @param MessageBusInterface $messageBus
     * @param VpnServerRepository $serverRepository
     *
     * @return JsonResponse
     */
    #[Route('/api/vpn_connection', name: 'add_vpn_connection', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/vpn_connection',
        summary: 'Add new VPN connection',
        tags: ['VPN Connections'],
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                description: 'User id who made connection',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
            new OA\Parameter(
                name: 'ip',
                description: 'IP address from which the connection has been established',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '192.168.0.1'
                )
            ),
            new OA\Parameter(
                name: 'country',
                description: 'A country from where the connection has been established',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'ru'
                )
            ),
            new OA\Parameter(
                name: 'server_id',
                description: 'ID of VPN server to which the connection has been established',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'protocol',
                description: 'A protocol by what the connection has been established',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['WireGuard', 'OpenVPN (UDP)', 'OpenVPN (TCP)']
                )
            ),
            new OA\Parameter(
                name: 'duration',
                description: 'The time that user spent on the server',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'number',
                    example: 100.01
                )
            ),
            new OA\Parameter(
                name: 'description',
                description: 'A description of the connection',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'a free connection'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '404',
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function addVpnConnection(
        UsefulToolsHelper $usefulToolsHelper,
        #[MapRequestPayload]
        VpnConnectionAddDto $dto,
        MessageBusInterface $messageBus,
        VpnServerRepository $serverRepository
    ): JsonResponse {
        $vpnConnection = $this->vpnConnectionRepository->add($dto);

        $data = [
            'id' => $vpnConnection->getId(),
        ];

        /** @var VpnServer $server */
        $server = $serverRepository->find($dto->server_id);

        $message = new AddClient(
            vpnServer: new VpnServerDto($server->getId(), $server->getIp(), $server->getUserName(), $server->getPassword(), $server->getProtocol()),
            clientName: $vpnConnection->getClientName()
        );
        $messageBus->dispatch($message);

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }
}
