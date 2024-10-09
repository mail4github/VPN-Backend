<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DeviceAddDto;
use App\Dto\DevicesGetDto;
use App\Entity\Device;
use App\Entity\User;
use App\Service\UsefulToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Class DeviceController.
 */
class DeviceController extends AbstractController
{
    /**
     * Retrieves a list of devices that user has.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param DevicesGetDto          $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/devices', name: 'devices', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/devices',
        summary: 'Retrieves a list of devices that user has',
        tags: ['User Devices']
    )]
    #[OA\Parameter(
        name: 'sort_by',
        in: 'query',
        schema: new OA\Schema(
            type: 'string',
            default: 'connected',
            enum: ['ip', 'name', 'connected']
        )
    )]
    #[OA\Parameter(
        name: 'sort_order',
        in: 'query',
        schema: new OA\Schema(
            type: 'string',
            default: 'desc',
            enum: ['asc', 'desc']
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'A list of devices',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'ip', type: 'string'),
                new OA\Property(property: 'user_id', type: 'integer'),
                new OA\Property(property: 'active', type: 'boolean'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'country', type: 'string'),
                'created' => new OA\Property(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property('date', type: 'string', example: '2024-03-24 18:03:17.000000'),
                            new OA\Property('timezone_type', type: 'integer', example: 3),
                            new OA\Property('timezone', type: 'string', example: 'UTC'),
                        ]
                    )
                ),
                new OA\Property(
                    'modified',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property('date', type: 'string', example: '2024-03-24 18:03:17.000000'),
                            new OA\Property('timezone_type', type: 'integer', example: 3),
                            new OA\Property('timezone', type: 'string', example: 'UTC'),
                        ]
                    )
                ),
                new OA\Property(
                    'connected',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property('date', type: 'string', example: '2024-03-24 18:03:17.000000'),
                            new OA\Property('timezone_type', type: 'integer', example: 3),
                            new OA\Property('timezone', type: 'string', example: 'UTC'),
                        ]
                    )
                ),
                new OA\Property(property: 'type', type: 'string', example: 'phone'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request parameters'
    )]
    public function devices(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        DevicesGetDto $dto
    ): JsonResponse {
        // Get parameters from the request
        $sortBy = $dto->sort_by ?: 'connected'; // can have the following values: ip, name, connected

        $sortOrder = $dto->sort_order ?: 'desc'; // can have the following values: asc, desc

        // Query the database based on parameters
        $query_arr = [];

        // Convert sortBy to camelCase
        $sortBy = $usefulToolsHelper->camelCase($sortBy);

        $sort_arr = [$sortBy => $sortOrder];

        $devices = [];

        $devices = $entityManager->getRepository(Device::class)->findBy($query_arr, $sort_arr);

        // Prepare the data to be serialized
        $data = [];
        foreach ($devices as $device) {
            $data[] = $this->fillInResult($device);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Add new device.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param DeviceAddDto           $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/device', name: 'addDevice', methods: [Request::METHOD_POST])]
    #[OA\Post(
        summary: 'Add new device',
        tags: ['User Devices'],
        parameters: [
            new OA\Parameter(
                name: 'ip',
                description: 'IP address of device',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '192.168.1.1'
                )
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Name of device',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'iPhone 11 Pro'
                )
            ),
            new OA\Parameter(
                name: 'active',
                description: 'If 1 then this device is active device',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 1,
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'fingerprint',
                description: 'Fingerprint of device',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'AFF123BC132'
                )
            ),
            new OA\Parameter(
                name: 'country',
                description: 'Country where this device is located',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'ru'
                )
            ),
            new OA\Parameter(
                name: 'type',
                description: 'Type of the device like `phone`, `desktop`, `notepad` etc.',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'phone'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        'id' => new OA\Property(type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function addDevice(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload]
        DeviceAddDto $dto
    ): JsonResponse {
        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $data = [];

        // Look the database for a device with same ip
        $devices = $entityManager->getRepository(Device::class)->findBy(['ip' => $dto->ip, 'userId' => $userId, 'fingerprint' => $dto->fingerprint], null, 1);
        if ($devices && \count($devices) > 0) {
            return $usefulToolsHelper->generate_answer('', 'A device with the same ip and fingerprint already exists', 'ERROR_ADD_DEVICE_6', 409);
        }

        $device = new Device();
        $device->setUserId($userId);
        $device->setIp($dto->ip);
        $device->setActive($dto->active);
        $device->setName($dto->name);
        $device->setFingerprint($dto->fingerprint);
        $device->setCountry($dto->country);
        $device->setType($dto->type);

        $device->setCreated(new \DateTime());
        $device->setModified(new \DateTime());
        $device->setConnected(new \DateTime());

        // tell Doctrine to save the device
        $entityManager->persist($device);

        // actually executes the querie
        $entityManager->flush();

        $data['id'] = $device->getId();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Remove device.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @return JsonResponse
     */
    #[Route('/api/device/{id}', name: 'remove_device', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        summary: 'Delete device',
        tags: ['User Devices'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of device',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        'id' => new OA\Property(type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function removeDevice(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $data = [];

        $db_res = $entityManager->getRepository(Device::class)->findBy(['id' => $id], null, 1);

        if (isset($db_res) && \count($db_res) > 0) {
            $device = $db_res[0];

            $entityManager->remove($device);

            $entityManager->flush();

            $data['id'] = $device->getId();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Device not found', 'ERROR_REMOVE_DEVICE_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * On / off active.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     * @param Request                $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/device/active/{id}', name: 'toggle_active_device', methods: [Request::METHOD_POST])]
    #[OA\Post(
        summary: 'On / off active',
        tags: ['User Devices'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of device',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'active',
                description: 'The value 1 means device is active, 0 - device is not active',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 0
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'active', type: 'boolean'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function toggleActiveDevice(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $id,
        Request $request
    ): JsonResponse {
        $data = [];

        $db_res = $entityManager->getRepository(Device::class)->findBy(['id' => $id], null, 1);

        if (isset($db_res) && \count($db_res) > 0) {
            $device = $db_res[0];

            $active = 1 == (int) $request->request->get('active'); // device is active
            $device->setActive($active);

            // save the $device in Doctrine
            $entityManager->persist($device);

            // actually executes the querie
            $entityManager->flush();

            $data['id'] = $device->getId();
            $data['active'] = $device->isActive();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Device not found', 'ERROR_ON_OFF_ACTIVE_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    private function fillInResult(Device $device): array
    {
        return [
            'id' => $device->getId() ?: 0,
            'ip' => $device->getIp(),
            'user_id' => $device->getUserId(),
            'active' => $device->isActive(),
            'name' => $device->getName(),
            'country' => $device->getCountry(),
            'created' => $device->getCreated(),
            'modified' => $device->getModified(),
            'connected' => $device->getConnected(),
            'type' => $device->getType(),
        ];
    }
}
