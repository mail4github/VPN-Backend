<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\WalletAddDto;
use App\Dto\WalletsGetDto;
use App\Entity\User;
use App\Entity\Wallet;
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
 * Class WalletController.
 */
class WalletController extends AbstractController
{
    /**
     * Retrieves a list of wallets that user has.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param WalletsGetDto          $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/wallets', name: 'wallets', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/wallets',
        summary: 'Retrieves a list of wallets that user has',
        security: [['Bearer']],
        tags: ['User Wallets'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort wallets.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'address',
                    enum: ['address', 'name']
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
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of wallets',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'address', type: 'string'),
                            new OA\Property(property: 'user_id', type: 'integer'),
                            new OA\Property(property: 'active', type: 'boolean'),
                            new OA\Property(property: 'name', type: 'string'),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function wallets(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        WalletsGetDto $dto
    ): JsonResponse
    {
        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        // Query the database based on the user id
        $query_arr = ['userId' => $userId];

        $sort_arr = [$usefulToolsHelper->camelCase($dto->sort_by) => $dto->sort_order];

        $wallets = $entityManager->getRepository(Wallet::class)->findBy($query_arr, $sort_arr);
        // Prepare the data to be serialized
        $data = [];
        foreach ($wallets as $wallet) {
            $data[] = [
                'id' => $wallet->getId() ?: 0,
                'address' => $wallet->getAddress(),
                'user_id' => $wallet->getUserId(),
                'active' => $wallet->isActive(),
                'name' => $wallet->getName(),
            ];
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Add new wallet.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param WalletAddDto           $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/wallet', name: 'add_wallet', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/wallet',
        summary: 'Add new wallet',
        security: [['Bearer']],
        tags: ['User Wallets'],
        parameters: [
            new OA\Parameter(
                name: 'address',
                description: 'Address of wallet',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Name of wallet',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'active',
                description: 'If 1 then this wallet is active wallet',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('id', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function addWallet(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload]
        WalletAddDto $dto
    ): JsonResponse {
        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $data = [];

        // Look the database for a wallet with same address
        $wallet = $entityManager->getRepository(Wallet::class)->findOneBy(['address' => $dto->address, 'userId' => $userId]);
        if (null != $wallet) {
            return $usefulToolsHelper->generate_answer('', 'A wallet with the same address already exists', 'ERROR_ADD_WALLET_6', 409);
        }

        if ($dto->active) {
            // Make all wallets, which belong to this user, not active
            $all_wallets = $entityManager->getRepository(Wallet::class)->findBy(['userId' => $userId]);
            foreach ($all_wallets as $wallet) {
                $wallet->setActive(false);
                $entityManager->persist($wallet);
            }
        }

        $wallet = new Wallet();
        $wallet->setUserId($userId);
        $wallet->setAddress($dto->address);
        $wallet->setActive($dto->active);
        $wallet->setName($dto->name);

        // tell Doctrine to save the wallet
        $entityManager->persist($wallet);

        // actually executes the querie
        $entityManager->flush();

        $data['id'] = $wallet->getId();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Remove wallet.
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
    #[Route('/api/wallet/{id}', name: 'remove_wallet', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/wallet/{id}',
        summary: 'Delete wallet',
        security: [['Bearer']],
        tags: ['User Wallets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of wallet',
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
                    properties: [
                        new OA\Property('id', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function removeWallet(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse
    {
        if (empty($id)) {
            return $usefulToolsHelper->generate_answer('', 'You have to specify the wallet id', 'ERROR_REMOVE_WALLET_1', 404);
        }

        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $wallet = $entityManager->getRepository(Wallet::class)->findOneBy(['id' => $id, 'userId' => $userId]);
        if (null !== $wallet) {
            $entityManager->remove($wallet);
            $entityManager->flush();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Wallet not found', 'ERROR_REMOVE_WALLET_2', 404);
        }


        // Return JSON response
        return $usefulToolsHelper->generate_answer();
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
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/wallet/active/{id}', name: 'on_off_active', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/wallet/active/{id}',
        summary: 'On / off active',
        security: [['Bearer']],
        tags: ['User Wallets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of wallet',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'active',
                description: 'The value 1 means wallet is active, 0 - wallet is not active',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('id', type: 'integer'),
                        new OA\Property('active', type: 'boolean'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function toggleActive(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        EntityManagerInterface $entityManager,
        int $id,
        Request $request
    ): JsonResponse
    {
        $userId = 1;
        if (!empty($user) && !empty($user->getId())) {
            $userId = $user->getId();
        }

        $data = [];

        $wallet = $entityManager->getRepository(Wallet::class)->findOneBy(['id' => $id, 'userId' => $userId]);

        if (null != $wallet) {
            if (1 == (int) $request->request->get('active')) {
                // Make all wallets, which belong to this user, not active
                $all_wallets = $entityManager->getRepository(Wallet::class)->findBy(['userId' => $userId]);
                foreach ($all_wallets as $wallet) {
                    $wallet->setActive(false);
                    $entityManager->persist($wallet);
                }
            }

            $active = 1 == (int) $request->request->get('active'); // wallet is active
            $wallet->setActive($active);

            // save the $wallet in Doctrine
            $entityManager->persist($wallet);
            // actually executes the querie
            $entityManager->flush();

            $data['id'] = $wallet->getId();
            $data['active'] = $wallet->isActive();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Wallet not found', 'ERROR_ON_OFF_ACTIVE_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }
}
