<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UsersGetDto;
use App\Dto\UserUpdateDto;
use App\Entity\User;
use App\Service\UsefulToolsHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * Retrieves a list of users.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param UsersGetDto            $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/users', name: 'list_of_users', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrator/users',
        summary: 'Retrieves a list of users',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort users based on specified criteria',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'login',
                    enum: ['login', 'email', 'owns_servers']
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
                name: 'login',
                description: 'Only users will be included whose login name looks like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'vasia73'
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
                description: 'Return the `limit` rows maximum',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 24
                )
            ),
        ]
    )]
    public function listOfUsers(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapQueryString]
        UsersGetDto $dto
    ): JsonResponse {
        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'usr.id',
                'usr.login',
                'usr.email',
                'COUNT(srv.created_by) AS owns_servers',
                'usr.picture',
                '-100 AS on_hold'
            ) // the "on_hold" value must be coded in future
            ->from('app.public."user"', 'usr')
            ->leftJoin('usr', 'vpn_server', 'srv', 'srv.created_by = usr.id')
            ->groupBy('usr.id')
            ->setFirstResult($dto->offset)
            ->setMaxResults($dto->limit)
        ;

        if (!empty($dto->login)) {
            $queryBuilder->where(
                $queryBuilder->expr()->like('usr.login', ':login')
            );
            $queryBuilder->setParameter('login', '%'.$dto->login.'%');
        }

        switch ($dto->sort_by) {
            case 'owns_servers':
                $queryBuilder->orderBy('CASE WHEN COUNT(srv.created_by) > 0 THEN 1 ELSE 0 END', $dto->sort_order);
                $queryBuilder->addOrderBy('usr.login', 'ASC');
                break;
            default:
                $queryBuilder->orderBy($dto->sort_by, $dto->sort_order);
                $queryBuilder->addOrderBy('usr.login', 'ASC');
                break;
        }
        $users = $queryBuilder->execute()->fetchAll();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($users);
    }

    /**
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param string                 $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/user/{id}', name: 'get_user_by_id', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrator/user/{id}',
        summary: 'Retrieves a user based on the user id.',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 123
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A record, which has a value called `results`, which contains the user data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'login',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'email',
                            type: 'string',
                            example: 'useremail@mail.ru'
                        ),
                        new OA\Property(
                            property: 'owns_servers',
                            type: 'integer',
                            example: 10
                        ),
                        new OA\Property(
                            property: 'picture',
                            type: 'string',
                            example: 'data:image/png;base64, iVBORw0KG...'
                        ),
                        new OA\Property(
                            property: 'created_at',
                            type: 'string',
                            example: '2024-02-22 10:49:18'
                        ),
                        new OA\Property(
                            property: 'on_hold',
                            type: 'number',
                            example: -100
                        ),
                        new OA\Property(
                            property: 'total_earned',
                            type: 'number',
                            example: 25.01
                        ),
                        new OA\Property(
                            property: 'total_spent',
                            type: 'number',
                            example: 51.11
                        ),
                        new OA\Property(
                            property: 'wallets',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'integer',
                                        example: 3
                                    ),
                                    new OA\Property(
                                        property: 'address',
                                        type: 'string',
                                        example: '0xdd7878787hhJHGJHGDHGDD7878787HHJHGJHG781'
                                    ),
                                    new OA\Property(
                                        property: 'active',
                                        type: 'boolean'
                                    ),
                                    new OA\Property(
                                        property: 'name',
                                        type: 'string',
                                        example: 'this is my valued wallet'
                                    ),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function getUserById(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        string $id
    ): JsonResponse {
        // Query the database based on the user id

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('usr.id', 'usr.login', 'usr.email', 'usr.picture', 'usr.created_at')
            ->from('app.public."user"', 'usr')
            ->where('usr.id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $id)
        ;
        $users = $queryBuilder->execute()->fetchAll();
        if (null != $users && $users && \count($users) > 0) {
            $user = $users[0];
            $user['on_hold'] = -200; // the "on_hold" value must be coded in future
            $user['total_earned'] = 10; // the "total_earned" value must be coded in future
            $user['total_spent'] = 20; // the "total_spent" value must be coded in future

            // Getting list of wallets that belong to user
            $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('wlt.id', 'wlt.address', 'wlt.active', 'wlt.name')
                ->from('app.public."wallet"', 'wlt')
                ->where('wlt.user_id = :user_id')
                ->setParameter('user_id', $id)
            ;
            $user['wallets'] = $queryBuilder->execute()->fetchAll();

            // Getting number of VPN servers created by this user
            $user['owns_servers'] = 0;
            $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
            $queryBuilder
                ->select('COUNT(*) AS owns_servers')
                ->from('app.public."vpn_server"', 'vpn')
                ->where('vpn.created_by = :user_id')
                ->setMaxResults(1)
                ->setParameter('user_id', $id)
            ;
            $owns_servers = $queryBuilder->execute()->fetchAll();
            if (null != $owns_servers && $owns_servers && \count($owns_servers) > 0) {
                $user['owns_servers'] = $owns_servers[0]['owns_servers'];
            }
        } else {
            return $usefulToolsHelper->generate_answer('', 'User not found', 'ERROR_GET_USER_1', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($user);
    }

    /**
     * Update  user.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param string                 $id
     * @param UserUpdateDto          $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/user/{id}', name: 'update_user_by_id', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/administrator/user/{id}',
        summary: 'Update user.',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'email',
                description: 'User email',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'vasia@mail.ru'
                )
            ),
            new OA\Parameter(
                name: 'login',
                description: 'User login name',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'vasia1976'
                )
            ),
            new OA\Parameter(
                name: 'password',
                description: 'User password',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string'
                )
            ),
            new OA\Parameter(
                name: 'picture',
                description: 'User picture in BASE64 format like `data:image/png;base64, iVBORw0KG...`',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'data:image/png;base64, iVBORw0KG...'
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
    public function updateUser(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        string $id,
        #[MapRequestPayload]
        UserUpdateDto $dto
    ): JsonResponse {
        $data = [];

        // Query the database based on the user id
        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('usr.id')
            ->from('app.public."user"', 'usr')
            ->where('usr.id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $id)
        ;
        $users = $queryBuilder->execute()->fetchAll();
        if (null == $users || !$users || \count($users) <= 0) {
            return $usefulToolsHelper->generate_answer('', 'User not found', 'ERROR_UPDATE_USER_2', 404);
        }

        $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
        $date = new \DateTime();
        $queryBuilder
            ->update('app.public."user"', 'usr')
            ->set('updated_at', "'".$date->format('Y-m-d H:i:s')."'")
            ->where('usr.id = :id')
            ->setParameter('id', $id)
        ;

        // add values that will be updated

        if (null !== $dto->email) {
            $queryBuilder
                ->set('email', ':email')
                ->setParameter('email', $dto->email)
            ;
        }

        if (null !== $dto->login) {
            $queryBuilder
                ->set('login', ':login')
                ->setParameter('login', $dto->login)
            ;
        }

        if (null !== $dto->password) {
            $queryBuilder
                ->set('password', ':password')
                ->setParameter('password', $dto->password)
            ;
        }

        if (null !== $dto->picture) {
            $queryBuilder
                ->set('picture', ':picture')
                ->setParameter('picture', $dto->picture)
            ;
        }

        $res = $queryBuilder->execute();
        if (!isset($res) || !$res) {
            return $usefulToolsHelper->generate_answer('', 'Some error occurred. Not updated.', 'ERROR_UPDATE_USER_4', 404);
        }

        $data['id'] = $id;

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }
}
