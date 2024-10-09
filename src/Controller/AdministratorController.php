<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\AdministratorAddDto;
use App\Dto\AdministratorsGetDto;
use App\Entity\Administrator;
use App\Entity\Adminrole;
use App\Entity\User;
use App\Repository\AdministratorRepository;
use App\Service\UsefulToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdministratorController.
 */
class AdministratorController extends AbstractController
{
    private AdministratorRepository $administratorRepository;

    public function __construct(
        AdministratorRepository $administratorRepository,
    ) {
        $this->administratorRepository = $administratorRepository;
    }

    /**
     * Retrieves a list of administrators.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param AdministratorsGetDto   $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrators', name: 'administrators', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrators',
        summary: 'Retrieves a list of administrators',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort administrators',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['created', 'login', 'last_login', 'role']
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
                name: 'search',
                description: 'Retrieve list of admins which have login or description or roles like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'admin1'
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
                description: 'Return the number of values not exceeding that specified in the parameter: `limit`',
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
                response: 200,
                description: 'A list of administrators',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property('id', type: 'integer'),
                                    new OA\Property('created', type: 'string', example: '2024-04-10 17:38:43'),
                                    new OA\Property('modified', type: 'string', example: '2024-04-10 17:38:43'),
                                    new OA\Property('last_login', type: 'string', example: '2024-04-10 17:38:43'),
                                    new OA\Property('login', type: 'string', example: 'admin1'),
                                    new OA\Property('description', type: 'string', example: 'This is super puper administrator'),
                                    new OA\Property('pgp_public_key', type: 'string', example: '-----BEGIN PGP PUBLIC KEY BLOCK-----xo0EZjdrlQEEANgULnzjb6yfEYHUes-----END PGP PUBLIC KEY BLOCK-----'),
                                    new OA\Property('superadmin', type: 'boolean'),
                                    new OA\Property('blocked', type: 'boolean'),
                                    new OA\Property('roles_list', type: 'string', example: 'Manager, Super Manager'),
                                    new OA\Property('roles', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property('name', type: 'string', example: 'Manager'),
                                            new OA\Property('permissions', type: 'array', items: new OA\Items(
                                                properties: [
                                                    new OA\Property('id', type: 'integer'),
                                                    new OA\Property('name', type: 'string', example: 'read_users_list'),
                                                    new OA\Property('read_only', type: 'boolean'),
                                                    new OA\Property('full_control', type: 'boolean'),
                                                ],
                                                type: 'object'
                                            )),
                                        ],
                                        type: 'object'
                                    )),
                                ],
                                type: 'object'
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function administrators(
        UsefulToolsHelper $usefulToolsHelper,
        #[MapQueryString]
        AdministratorsGetDto $dto
    ): JsonResponse {
        
        $administrators = $this->administratorRepository->getListOfAdministrators(
            NULL,
            $dto->search,
            $dto->limit,
            $dto->sort_by,
            $dto->sort_order,
            $dto->offset
        );

        for ($i = 0; $i < \count($administrators); ++$i) {
            if (null === $administrators[$i]['roles']) {
                $administrators[$i]['roles'] = [];
            } else {
                $administrators[$i]['roles'] = json_decode($administrators[$i]['roles'], true);
            }
            $administrators[$i]['roles_list'] = '';
            foreach ($administrators[$i]['roles'] as $role) {
                $administrators[$i]['roles_list'] = $administrators[$i]['roles_list'].(!empty($administrators[$i]['roles_list']) ? ', ' : '').$role['name'];
            }
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($administrators);
    }

    /**
     * Add new administrator.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param EntityManagerInterface $entityManager
     * @param AdministratorAddDto    $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/account', name: 'add_administrator', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/administrator/account',
        summary: 'Add new administrator',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'login',
                description: 'login name',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'admin1'
                )
            ),
            new OA\Parameter(
                name: 'pgp_public_key',
                description: 'A public PGP key of administrator',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '-----BEGIN PGP PUBLIC KEY BLOCK-----xo0EZjdrlQEEANgULnzjb6yfEYHUes-----END PGP PUBLIC KEY BLOCK-----'
                )
            ),
            new OA\Parameter(
                name: 'description',
                description: 'A comment about admin',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: "''",
                    example: 'This is a super admin'
                )
            ),
            new OA\Parameter(
                name: 'superadmin',
                description: 'The administrator has (or has not) access to the administration panel',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    default: false
                )
            ),
            new OA\Parameter(
                name: 'blocked',
                description: 'Is this administrator disabled or not',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    default: false
                )
            ),
            new OA\Parameter(
                name: 'roles',
                description: 'a JSON text with array of role ids. Example:`:` `[1,2]`',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: '[]',
                    example: '[3, 4]'
                )
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
    public function addAdministrator(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload]
        AdministratorAddDto $dto
    ): JsonResponse {
        $data = [];

        // Looking out the database for an administrator with same login
        $administrator = $this->administratorRepository->findOneBy(['login' => $dto->login]);
        if (NULL !== $administrator) {
            return $usefulToolsHelper->generate_answer('', 'An administrator with the same login already exists', 'ERROR_ADD_ADMINISTRATOR_6', 409);
        }

        $administrator = new Administrator();
        $administrator->setCreated(new \DateTime());
        $administrator->setLogin($dto->login);
        $administrator->setPgpPublicKey($dto->pgp_public_key);

        if (null !== $dto->description) {
            $administrator->setDescription($dto->description);
        }

        if (null !== $dto->superadmin) {
            $administrator->setSuperadmin($dto->superadmin);
        }

        if (null !== $dto->blocked) {
            $administrator->setBlocked($dto->blocked);
        }

        // tell Doctrine to save the administrator
        $entityManager->persist($administrator);

        $data['id'] = $administrator->getId();

        // Add roles to this administrator
        if (null !== $dto->roles) {
            foreach ($dto->roles as $role_id) {
                $adminrole = new Adminrole();
                $adminrole->setCreated(new \DateTime());
                $adminrole->setAdminId($administrator->getId());
                $adminrole->setRoleId($role_id);
                $entityManager->persist($adminrole);
            }
        }

        // actually executes the querie
        $entityManager->flush();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param int                    $id
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/account/{id}', name: 'get_administrator', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrator/account/{id}',
        summary: 'Retrieves an administrator based on the administrator id',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: "Administrator's ID",
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('id', type: 'integer'),
                        new OA\Property('created', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('modified', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('last_login', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('login', type: 'string', example: 'admin1'),
                        new OA\Property('description', type: 'string', example: 'This is super puper administrator'),
                        new OA\Property('pgp_public_key', type: 'string', example: '-----BEGIN PGP PUBLIC KEY BLOCK-----xo0EZjdrlQEEANgULnzjb6yfEYHUes-----END PGP PUBLIC KEY BLOCK-----'),
                        new OA\Property('superadmin', type: 'boolean'),
                        new OA\Property('blocked', type: 'boolean'),
                        new OA\Property('roles', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property('name', type: 'string', example: 'Manager'),
                                new OA\Property('permissions', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property('id', type: 'integer'),
                                        new OA\Property('name', type: 'string', example: 'read_users_list'),
                                        new OA\Property('read_only', type: 'boolean'),
                                        new OA\Property('full_control', type: 'boolean'),
                                    ],
                                    type: 'object'
                                )),
                            ],
                            type: 'object'
                        )),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function getAdministrator(
        UsefulToolsHelper $usefulToolsHelper,
        int $id
    ): JsonResponse {

        $administrators = $this->administratorRepository->getListOfAdministrators(
            $id,
            '',
            1
        );
        
        if ($administrators && \count($administrators) > 0) {
            $data = $administrators[0];
            if (null === $data['roles']) {
                $data['roles'] = [];
            } else {
                $data['roles'] = json_decode($data['roles']);
            }
        } else {
            return $usefulToolsHelper->generate_answer('', 'Administrator not found', 'ERROR_GET_ADMINISTRATOR_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Update administrator.
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
    #[Route('/api/administrator/account/{id}', name: 'update_administrator', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/administrator/account/{id}',
        summary: 'Update an administrator',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'the administrator id who has to be updated',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'login',
                description: 'login name',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'admin1'
                )
            ),
            new OA\Parameter(
                name: 'pgp_public_key',
                description: 'A public PGP key of administrator',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: '-----BEGIN PGP PUBLIC KEY BLOCK-----xo0EZjdrlQEEANgULnzjb6yfEYHUes-----END PGP PUBLIC KEY BLOCK-----'
                )
            ),
            new OA\Parameter(
                name: 'description',
                description: 'A comment about admin',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: "''",
                    example: 'This is a super admin'
                )
            ),
            new OA\Parameter(
                name: 'superadmin',
                description: 'If `true` then this administrator has access to the administration panel',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    default: false
                )
            ),
            new OA\Parameter(
                name: 'blocked',
                description: 'Is this administrator disabled or not',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    default: false
                )
            ),
            new OA\Parameter(
                name: 'roles',
                description: 'A json array with the role names',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: '[]',
                    example: '["role1", "roole2"]'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('id', type: 'integer'),
                        new OA\Property('created', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('modified', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('last_login', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('login', type: 'string', example: 'admin1'),
                        new OA\Property('description', type: 'string', example: 'This is super puper administrator'),
                        new OA\Property('pgp_public_key', type: 'string', example: '-----BEGIN PGP PUBLIC KEY BLOCK-----xo0EZjdrlQEEANgULnzjb6yfEYHUes-----END PGP PUBLIC KEY BLOCK-----'),
                        new OA\Property('superadmin', type: 'boolean'),
                        new OA\Property('blocked', type: 'boolean'),
                        new OA\Property('roles', type: 'array', items: new OA\Items(type: 'string')),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Administrator not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('message', type: 'string', example: "Administrator with id '1' was not found."),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('message', type: 'string', example: 'Missing or invalid data.'),
                    ]
                )
            ),
        ]
    )]
    public function updateAdministrator(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $id,
        Request $request
    ): JsonResponse {
        
        $administrator = $this->administratorRepository->findOneBy(['id' => $id]);
        if (NULL !== $administrator) {
            // Adding roles of this administrator
            if (null !== $request->request->get('roles')) {
                $roles = json_decode($request->request->get('roles'), true);
                if (null === $roles) {
                    throw new \Exception('Cannot parse JSON string passed in the \'roles\' param');
                }

                // Delete records in the adminrole table, associated with this admin
                $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
                $queryBuilder
                    ->delete('app.public."adminrole"', 'admrole')
                    ->where('admrole.admin_id = :id')
                    ->setParameter('id', $id)
                    ->execute()
                ;

                // Add new roles
                foreach ($roles as $role_id) {
                    $adminrole = new Adminrole();
                    $adminrole->setCreated(new \DateTime());
                    $adminrole->setAdminId($administrator->getId());
                    $adminrole->setRoleId($role_id);
                    $entityManager->persist($adminrole);
                }
            }

            if (null !== $request->request->get('login') && mb_strlen($request->request->get('login')) > 2) {
                // Looking out the database for an administrator with same login
                $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
                $queryBuilder
                    ->select('admin.id')
                    ->from('app.public."administrator"', 'admin')
                    ->where('admin.login = :login')->setParameter('login', $request->request->get('login'))
                    ->andWhere('admin.id <> :admin_id')->setParameter('admin_id', $id)
                    ->setMaxResults(1)
                ;
                $sameLoginAdministrators = $queryBuilder->execute()->fetchAll();
                
                if (NULL !== $sameLoginAdministrators && count($sameLoginAdministrators) > 0) {
                    return $usefulToolsHelper->generate_answer('', 'An administrator with the same login already exists', 'ERROR_UPDATE_ADMINISTRATOR_3', 409);
                }

                $administrator->setLogin($request->request->get('login'));
            }

            if (null !== $request->request->get('pgp_public_key') && mb_strlen($request->request->get('pgp_public_key')) > 2) {
                $administrator->setPgpPublicKey($request->request->get('pgp_public_key'));
            }

            if (null !== $request->request->get('description')) {
                $administrator->setDescription($request->request->get('description'));
            }

            if (null !== $request->request->get('superadmin')) {
                $administrator->setSuperadmin($request->request->get('superadmin'));
            }

            if (null !== $request->request->get('blocked')) {
                $administrator->setBlocked($request->request->get('blocked'));
            }

            // tell Doctrine to save the administrator
            $entityManager->persist($administrator);

            // actually executes the querie
            $entityManager->flush();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Administrator not found', 'ERROR_UPDATE_ADMINISTRATOR_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }

    /**
     * Remove administrator.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param EntityManagerInterface $entityManager
     * @param int                    $id
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/account/{id}', name: 'remove_administrator', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/administrator/account/{id}',
        summary: 'Deletes an administrator based on the administrator id',
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: "Administrator's ID",
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
                description: 'Successful'
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function removeAdministrator(
        UsefulToolsHelper $usefulToolsHelper,
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $db_res = $entityManager->getRepository(Administrator::class)->findBy(['id' => $id], null, 1);

        if (isset($db_res) && \count($db_res) > 0) {
            $administrator = $db_res[0];

            // Delete records, associated with this admin, in the adminrole table
            $queryBuilder = $entityManager->getConnection()->createQueryBuilder();
            $queryBuilder
                ->delete('app.public."adminrole"', 'admrole')
                ->where('admrole.admin_id = :id')
                ->setParameter('id', $id)
                ->execute()
            ;

            $entityManager->remove($administrator);

            $entityManager->flush();
        } else {
            return $usefulToolsHelper->generate_answer('', 'Administrator not found', 'ERROR_REMOVE_ADMINISTRATOR_2', 404);
        }

        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }
}
