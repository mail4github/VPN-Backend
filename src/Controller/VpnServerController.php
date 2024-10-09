<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Message\VpnServerDto;
use App\Dto\Response\Transformer\VpnServerResponseDtoTransformer;
use App\Dto\VpnServerAddDto;
use App\Dto\VpnServersGetDto;
use App\Dto\VpnServerUpdateDto;
use App\Entity\FavoriteServer;
use App\Entity\User;
use App\Entity\VpnServer;
use App\Message\DeployServer;
use App\Repository\VpnServerRepository;
use App\Repository\UserRepository;
use App\Service\Ipdata\IpdataService;
use App\Service\UsefulToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Class VpnServerController.
 */
class VpnServerController extends AbstractController
{
    private VpnServerResponseDtoTransformer $vpnServerResponseDtoTransformer;
    private VpnServerRepository $vpnServerRepository;
    private UserRepository $userRepository;

    public function __construct(
        VpnServerResponseDtoTransformer $vpnServerResponseDtoTransformer,
        protected IpdataService $ipdataService,
        VpnServerRepository $vpnServerRepository,
        UserRepository $userRepository,
    ) {
        $this->vpnServerResponseDtoTransformer = $vpnServerResponseDtoTransformer;
        $this->vpnServerRepository = $vpnServerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieves a list of VPN servers based on specified parameters.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param VpnServersGetDto       $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/servers', name: 'vpn_servers', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/servers',
        summary: 'Retrieves a list of VPN servers',
        tags: ['VPN Servers'],
        parameters: [
            new OA\Parameter(
                name: 'pick_out',
                description: 'Pick out servers based on certain criteria.',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['all', 'subscribed', 'favorites', 'own']
                )
            ),
            new OA\Parameter(
                name: 'created_by',
                description: 'A user id. Filter servers by owner. Only servers, which have been created by user with that id will be added to the result.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 11
                )
            ),
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort servers based on specified criteria.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'connection_quality',
                    enum: ['connection_quality', 'created', 'price', 'user_name', 'country', 'ip', 'protocol']
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
                name: 'country',
                description: 'Filter servers by country. List of countries separated by comma',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'us,ru,de'
                )
            ),
            new OA\Parameter(
                name: 'for_free',
                description: 'Only servers that are available for free',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'limited_time_rent_available',
                description: 'Only servers with limited time rent available',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 0
                )
            ),
            new OA\Parameter(
                name: 'limited_traffic_rent_available',
                description: 'Only servers with limited traffic rent available',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'protocol',
                description: 'Filter servers by protocol.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['WireGuard', 'OpenVPN (UDP)', 'OpenVPN (TCP)']
                )
            ),
            new OA\Parameter(
                name: 'residential_ip',
                description: 'Only servers with residential IP',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'servers_with_public_ip',
                description: 'Only servers with public IP',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'The current page of the servers.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'The number of servers per page.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 10
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A list of VPN servers',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property('id', type: 'number'),
                            new OA\Property(
                                'network',
                                properties: [
                                    new OA\Property('ip'),
                                    new OA\Property('signal_level'),
                                    new OA\Property('protocol'),
                                    new OA\Property('network_type'),
                                ],
                                type: 'object'
                            ),
                            new OA\Property('country', type: 'string'),
                            new OA\Property('type', type: 'string'),
                            new OA\Property('is_favourite', type: 'boolean'),
                            new OA\Property(
                                'info',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'string'
                                )
                            ),
                            new OA\Property(
                                'creator',
                                properties: [
                                    new OA\Property('id', type: 'number'),
                                    new OA\Property('username', type: 'string'),
                                    new OA\Property('avatar', type: 'string'),
                                ],
                                type: 'object'
                            ),
                            new OA\Property('workload', type: 'number'),
                            new OA\Property('is_purchased', type: 'boolean'),
                            new OA\Property('used_value', type: 'number'),
                            new OA\Property('created', type: 'string'),
                            new OA\Property('modified', type: 'string'),
                            new OA\Property('wallet_address', type: 'string'),
                            new OA\Property('price', type: 'number'),
                            new OA\Property('service_commission', type: 'number'),
                            new OA\Property('maximum_active_connections', type: 'number'),
                            new OA\Property('traffic_vs_period', type: 'boolean'),
                            new OA\Property('test_packages', type: 'string'),
                            new OA\Property('paid_packages', type: 'string'),
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
    public function vpnServers(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload]
        VpnServersGetDto $dto
    ): JsonResponse {
        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }
        
        $servers = $this->vpnServerRepository->getServers($userId, $dto);

        // Prepare the data to be serialized
        $data = [];
        foreach ($servers as $server_arr) {
            $server = new VpnServer();

            // Convert an array to the VpnServer object
            foreach ($server_arr as $key => $value) {
                $funcName = 'set'.ucfirst($usefulToolsHelper->camelCase($key));
                $rp = new \ReflectionParameter(['App\Entity\VpnServer', $funcName], 0);
                $paramType = str_replace( '?', '',  (string) $rp->getType() ); 
                if ($paramType == 'DateTimeInterface') {
                    $value = new \DateTime($value);
                }
                elseif ($paramType == 'float') {
                    $value = floatval($value);
                }
                $server->$funcName($value);
            }

            $data[] = $this->vpnServerResponseDtoTransformer->transformFromVpnServer($server, $entityManager, $userId);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Retrieves a VPN server based on the server id.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/{id}', name: 'getServer', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/server/{id}',
        summary: 'Retrieves a VPN server based on the server id.',
        tags: ['VPN Servers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Server ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A record, which has a value called `results`, which contains the VPN server',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(
                                    property: 'network',
                                    properties: [
                                        new OA\Property(property: 'ip', type: 'string'),
                                        new OA\Property(property: 'signal_level', type: 'string'),
                                        new OA\Property(property: 'protocol', type: 'string'),
                                        new OA\Property(property: 'network_type', type: 'string'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'country', type: 'string'),
                                new OA\Property(property: 'type', type: 'string'),
                                new OA\Property(property: 'is_favourite', type: 'boolean'),
                                new OA\Property(
                                    property: 'info',
                                    type: 'array',
                                    items: new OA\Items(type: 'string')
                                ),
                                new OA\Property(
                                    property: 'creator',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'user_name', type: 'string'),
                                        new OA\Property(property: 'avatar', type: 'string'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'workload', type: 'number'),
                                new OA\Property(property: 'is_purchased', type: 'boolean'),
                                new OA\Property(property: 'used_value', type: 'number'),
                                new OA\Property(property: 'created', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'modified', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'wallet_address', type: 'string'),
                                new OA\Property(property: 'price', type: 'number'),
                                new OA\Property(property: 'service_commission', type: 'number'),
                                new OA\Property(property: 'maximum_active_connections', type: 'integer'),
                                new OA\Property(property: 'traffic_vs_period', type: 'boolean'),
                                new OA\Property(property: 'test_packages', type: 'string'),
                                new OA\Property(property: 'paid_packages', type: 'string'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid request parameters'),
        ]
    )]
    public function getServer(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $userId = 0;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $data = [];

        // Look the database for such server
        $server = $this->vpnServerRepository->findOneBy(['id' => $id]);
        if (empty($server)) {
            return $usefulToolsHelper->generate_answer('', 'No such server id', 'ERROR_GET_SERVER_2', 404);
        }

        // Prepare the data to be serialized
        if (!empty($server)) {
            $data = $this->vpnServerResponseDtoTransformer->transformFromVpnServer($server, $entityManager, $userId);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Add VPN server to favorites.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/favorite/{id}', name: 'add_to_favorites', methods: [Request::METHOD_PUT])]
    #[OA\Put(
        path: '/api/server/favorite/{id}',
        summary: 'Add VPN server to favorites',
        tags: ['VPN Servers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Server ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function addToFavorites(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $userId = 0;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        // Search database for this server
        $server = $this->vpnServerRepository->findOneBy(['id' => $id]);
        if (empty($server)) {
            return $usefulToolsHelper->generate_answer('', 'No such server id', 'ERROR_GET_SERVER_2', 404);
        }

        $favoriteServer = $entityManager->getRepository(FavoriteServer::class)->findOneBy(['userId' => $userId, 'serverId' => $id]);
        if (empty($favoriteServer)) {
            $favoriteServer = new FavoriteServer();
            $favoriteServer->setUserId($userId);
            $favoriteServer->setServerId($id);

            // save the $favoriteServer in Doctrine
            $entityManager->persist($favoriteServer);

            // actually executes the querie (the INSERT query)
            $entityManager->flush();
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }

    /**
     * Remove VPN server from favorites.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/favorite/{id}', name: 'remove_from_favorites', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/server/favorite/{id}',
        summary: 'Remove VPN server from favorites',
        tags: ['VPN Servers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Server ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function removeFromFavorites(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $userId = 0;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        // Search database for this server
        $server = $this->vpnServerRepository->findOneBy(['id' => $id]);
        if (empty($server)) {
            return $usefulToolsHelper->generate_answer('', 'No such server id', 'ERROR_GET_SERVER_2', 404);
        }

        $favoriteServer = $entityManager->getRepository(FavoriteServer::class)->findOneBy(['userId' => $userId, 'serverId' => $id]);
        if (!empty($favoriteServer)) {
            $entityManager->remove($favoriteServer);

            $entityManager->flush();
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }

    /**
     * Create new VPN server based on specified parameters.
     *
     * @param UsefulToolsHelper $usefulToolsHelper
     * @param User|null         $user
     * @param VpnServerAddDto   $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server', name: 'addServer', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/server',
        summary: 'Create new VPN server based on specified parameters',
        tags: ['VPN Servers'],
    )]
    #[OA\Parameter(
        name: 'ip',
        description: 'server IP',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: '192.168.1.1',
        ),
    )]
    #[OA\Parameter(
        name: 'user_name',
        description: "server's user name which is used to login to server",
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'password',
        description: "server's password which is used to login to server",
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'wallet_address',
        description: 'the address of wallet which is used for receiving rewards from the sale of packages for access to the VPN server',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: '0x123456abcdef123456abcdef123456abcdef',
        ),
    )]
    #[OA\Parameter(
        name: 'connection_quality',
        description: 'quality of connection to server; 0 - is best quality, 1 - fair quality, 2 - poor quality',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 0,
        ),
    )]
    #[OA\Parameter(
        name: 'service_commission',
        description: 'A commission for this service in percents',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'number',
            example: 0.1,
        ),
    )]
    #[OA\Parameter(
        name: 'maximum_active_connections',
        description: 'Number of maximum active connections. If zero then no limits',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 10,
        ),
    )]
    #[OA\Parameter(
        name: 'for_free',
        description: 'Is it possible to connect to this server for free',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 1,
        ),
    )]
    #[OA\Parameter(
        name: 'protocol',
        description: 'Possible values - WireGuard, OpenVPN (UDP), OpenVPN (TCP)',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            example: 'OpenVPN (UDP)',
        ),
    )]
    #[OA\Parameter(
        name: 'residential_ip',
        description: 'Is this server located at a residential area',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 1,
        ),
    )]
    #[OA\Parameter(
        name: 'traffic_vs_period',
        description: 'If true then the traffic is active. If false then period of time',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 1,
        ),
    )]
    #[OA\Parameter(
        name: 'test_packages',
        description: 'A JSON text with array of test packages',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            example: '{"traffic":[{"Mb":10,"price":1},{"Mb":100,"price":10},{"Gb":1000,"price":100}],"period":[{"days":10,"price":1},{"days":20,"price":3},{"days":30,"price":5}]}',
        ),
    )]
    #[OA\Parameter(
        name: 'paid_packages',
        description: 'A JSON text with array of paid packages',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            example: '{"traffic":[{"Mb":10,"price":1},{"Mb":100,"price":10},{"Gb":1000,"price":100}],"period":[{"days":10,"price":1},{"days":20,"price":3},{"days":30,"price":5}]}',
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                ),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 404,
        description: 'Invalid request parameters',
    )]
    public function addServer(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        #[MapRequestPayload]
        VpnServerAddDto $dto,
        MessageBusInterface $messageBus
    ): JsonResponse {
        $userId = 0;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $data = [];

        // Look the database for a server with same IP
        if (null !== $this->vpnServerRepository->findByIp($dto->ip)) {
            return $usefulToolsHelper->generate_answer('', 'A server with the same IP already exists', 'ERROR_ADD_SERVER_6', 409);
        }

        $server = new VpnServer();
        $server->setCountry($this->ipdataService->getGeolocation($dto->ip)->getCountryCode());

        $server->setIp($dto->ip);
        $server->setUserName($dto->user_name);
        $server->setPassword($dto->password);
        $server->setWalletAddress($dto->wallet_address);
        $server->setConnectionQuality($dto->connection_quality);
        $server->setServiceCommission($dto->service_commission);
        $server->setMaximumActiveConnections($dto->maximum_active_connections);
        $server->setForFree($dto->for_free);
        $server->setProtocol($dto->protocol);
        $server->setResidentialIp($dto->residential_ip);
        $server->setTrafficVsPeriod($dto->traffic_vs_period);
        $server->setTestPackages(json_encode($dto->test_packages));
        $server->setPaidPackages(json_encode($dto->paid_packages));

        $server->setCreated(new \DateTime());
        $server->setCreatedBy($userId);

        $this->vpnServerRepository->save($server);

        $data['id'] = $server->getId();

        $deployMessage = new DeployServer(new VpnServerDto($data['id'], $dto->ip, $dto->user_name, $dto->password, $dto->protocol));
        $messageBus->dispatch($deployMessage);

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Update VPN server.
     *
     * @param UsefulToolsHelper  $usefulToolsHelper
     * @param User|null          $user
     * @param VpnServerUpdateDto $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/{id}', name: 'updateServer', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/server/{id}',
        summary: 'Update VPN server.',
        tags: ['VPN Servers'],
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Server ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'country',
        description: 'server country',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'ip',
        description: 'server IP',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'user_name',
        description: "server's user name which is used to login to server",
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'password',
        description: "server's password which is used to login to server",
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'wallet_address',
        description: 'the address of wallet which is used for receiving rewards from the sale of packages for access to the VPN server',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'created_by',
        description: 'User id of owner of this server',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'connection_quality',
        description: 'quality of connection to server; 0 - is best quality, 1 - fair quality, 2 - poor quality',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'service_commission',
        description: 'A commission for this service in percents',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'number',
        ),
    )]
    #[OA\Parameter(
        name: 'maximum_active_connections',
        description: 'Number of maximum active connections. If zero then no limits',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'for_free',
        description: 'Is it possible to connect to this server for free',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'protocol',
        description: 'Possible values - WireGuard, OpenVPN (UDP), OpenVPN (TCP)',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'residential_ip',
        description: 'Is this server located at a residential area',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'traffic_vs_period',
        description: 'If true then the traffic is active. If false then period of time',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Parameter(
        name: 'test_packages',
        description: 'A JSON text with array of test packages',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Parameter(
        name: 'paid_packages',
        description: 'A JSON text with array of paid packages',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                ),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 404,
        description: 'Invalid request parameters',
    )]
    public function updateServer(
        UsefulToolsHelper $usefulToolsHelper,
        string $id,
        #[MapRequestPayload]
        VpnServerUpdateDto $dto
    ): JsonResponse {
        $data = [];

        // Query the database based on the server id
        $server = $this->vpnServerRepository->findOneBy(['id' => $id]);
        if (empty($server)) {
            return $usefulToolsHelper->generate_answer('', 'Server not found', 'ERROR_UPDATE_SERVER_1', 404);
        }

        // Get parameters from the request

        if (null !== $dto->country) {
            $server->setCountry($dto->country);
        }

        if (null !== $dto->ip) {
            $server->setCountry($this->ipdataService->getGeolocation($dto->ip)->getCountryCode());
            $server->setIp($dto->ip);
        }

        if (null !== $dto->user_name) {
            $server->setUserName($dto->user_name);
        }

        if (null !== $dto->password) {
            $server->setPassword($dto->password);
        }

        if (null !== $dto->wallet_address) {
            $server->setWalletAddress($dto->wallet_address);
        }

        if (null !== $dto->created_by) {
            $server->setCreatedBy($dto->created_by);
        }

        if (null !== $dto->connection_quality) {
            $server->setConnectionQuality($dto->connection_quality);
        }

        if (null !== $dto->service_commission) {
            $server->setServiceCommission($dto->service_commission);
        }

        if (null !== $dto->maximum_active_connections) {
            $server->setMaximumActiveConnections($dto->maximum_active_connections);
        }

        if (null !== $dto->for_free) {
            $server->setForFree($dto->for_free);
        }

        if (null !== $dto->protocol) {
            $server->setProtocol($dto->protocol);
        }

        if (null !== $dto->residential_ip) {
            $server->setResidentialIp($dto->residential_ip);
        }

        if (null !== $dto->traffic_vs_period) {
            $server->setTrafficVsPeriod($dto->traffic_vs_period);
        }

        if (null !== $dto->test_packages) {
            $server->setTestPackages($dto->test_packages);
        }

        if (null !== $dto->paid_packages) {
            $server->setPaidPackages($dto->paid_packages);
        }

        $this->vpnServerRepository->save($server);

        $data['id'] = $server->getId();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Delete VPN server.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/{id}', name: 'deleteServer', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/server/{id}',
        summary: 'Delete VPN server.',
        tags: ['VPN Servers'],
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Server ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                ),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 404,
        description: 'Invalid request parameters',
    )]
    public function deleteServer(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $data = [];

        // Query the database based on the server id
        $server = $this->vpnServerRepository->findOneBy(['id' => $id]);
        if (empty($server)) {
            return $usefulToolsHelper->generate_answer('', 'Server not found', 'ERROR_DELETE_SERVER_1', 404);
        }

        $data['id'] = $server->getId();

        $this->vpnServerRepository->remove($server, false);

        // Remove deleted server from the FavoriteServer table
        $favoriteServer = $entityManager->getRepository(FavoriteServer::class)->findOneBy(['serverId' => $id]);
        if (!empty($favoriteServer)) {
            $entityManager->remove($favoriteServer);
        }

        $entityManager->flush();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Retrieves an info about the VPN server owner.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $user_id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/server/provider_info/{user_id}', name: 'get_provider_info', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/server/provider_info/{user_id}',
        summary: 'Retrieves an info about the VPN server owner',
        tags: ['VPN Servers'],
    )]
    #[OA\Parameter(
        name: 'user_id',
        description: 'User ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'A few data about the user. Login name and picture. Picture is a string in BASE64 format like:`data:image/png;base64, iVBORw0KG...`',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'login',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'picture',
                    type: 'string',
                    example: 'data:image/png;base64, iVBORw0KG...',
                ),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request parameters',
    )]
    public function getProviderInfo(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $user_id
    ): JsonResponse {
        
        // Query the database based on the user id
        $user = $this->userRepository->findOneBy(['id' => $user_id]);
        
        if ( empty($user) ) {
            return $usefulToolsHelper->generate_answer('', 'User not found', 'ERROR_PROVIDER_INFO_1', 404);
        }
        
        // Return JSON response
        return $usefulToolsHelper->generate_answer(
            [
            'login' => $user->getLogin(),
            'picture' => $user->getPicture(),
            ]
        );
    }
}
