<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RoleAddDto;
use App\Dto\RolesGetDto;
use App\Entity\Role;
use App\Repository\RoleRepository;
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
 * Class RoleController.
 */
class RoleController extends AbstractController
{
    private RoleRepository $roleRepository;

    public function __construct(
        RoleRepository $roleRepository,
    ) {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Retrieves a list of roles.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param RolesGetDto            $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/roles', name: 'roles', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrator/roles',
        summary: 'Retrieves list of roles',
        security: [['Bearer']],
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Sort roles',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'created',
                    enum: ['created', 'name']
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
                description: 'Retrieve list of roles which have name like that value',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'superadmin1'
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
                response: 200,
                description: 'A list of administrator roles',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('id', type: 'integer'),
                        new OA\Property('created', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('modified', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('name', type: 'string', example: 'manager'),
                        new OA\Property('permissions', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function roles(
        UsefulToolsHelper $usefulToolsHelper,
        #[MapQueryString]
        RolesGetDto $dto
    ): JsonResponse {
        
        $roles = $this->roleRepository->getListOfRoles($dto);
        
        // Return JSON response
        return $usefulToolsHelper->generate_answer($roles);
    }

    /**
     * Add new role.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param RoleAddDto             $dto
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/role', name: 'add_role', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/administrator/role',
        summary: 'Create new role',
        security: [['Bearer']],
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                description: "Role's name",
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'superadmin1'
                )
            ),
            new OA\Parameter(
                name: 'permissions',
                description: "Role's list of permissions. A json string",
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: '[{"name":"read_users_list","read_only":true,"full_control":false}]'
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
    public function addRole(
        UsefulToolsHelper $usefulToolsHelper,
        #[MapRequestPayload]
        RoleAddDto $dto
    ): JsonResponse {
        $data = [];

        // Look the database for a role with same name
        if ( NULL !== $this->roleRepository->findOneBy(['name' => $dto->name]) ) {
            return $usefulToolsHelper->generate_answer('', 'A role with the same name already exists', 'ERROR_ADD_ROLE_1', 409);
        }

        $role = new Role();
        $role->setCreated(new \DateTime());
        $role->setName($dto->name);
        $role->setPermissions($dto->permissions);
        
        $this->roleRepository->save($role);

        $data['id'] = $role->getId();

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Read role.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/role/{id}', name: 'get_role', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/administrator/role/{id}',
        summary: 'Retrieves a role based on the role id',
        security: [['Bearer']],
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: "Role's ID",
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
                        new OA\Property('id', type: 'integer'),
                        new OA\Property('created', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('modified', type: 'string', example: '2024-04-10 17:38:43'),
                        new OA\Property('name', type: 'string', example: 'manager'),
                        new OA\Property('permissions', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid request parameters'
            ),
        ]
    )]
    public function getRole(
        UsefulToolsHelper $usefulToolsHelper,
        int $id
    ): JsonResponse {
        $data = [];

        $role = $this->roleRepository->findOneBy(['id' => $id]);
        if ( NULL == $role ) {
            return $usefulToolsHelper->generate_answer('', 'Role not found', 'ERROR_GET_ROLE_1', 404);
        }
        
        $data['id'] = $role->getId();
        $data['created'] = $role->getCreated()->format('Y-m-d H:i:s');
        $data['modified'] = $role->getModified()->format('Y-m-d H:i:s');
        $data['name'] = $role->getName();
        $data['permissions'] = $role->getPermissions();
        
        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Update role.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param int                    $id
     * @param Request                $request
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/role/{id}', name: 'update_role', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/administrator/role/{id}',
        summary: 'Updates a role based on the role id',
        security: [['Bearer']],
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: "Role's ID",
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'name',
                in: 'query',
                description: "Role's name",
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'superadmin1'
                )
            ),
            new OA\Parameter(
                name: 'permissions',
                in: 'query',
                description: "Role's list of permissions. A json string",
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: '[{"name":"read_users_list","read_only":true,"full_control":false}]'
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
    public function updateRole(
        UsefulToolsHelper $usefulToolsHelper,
        int $id,
        Request $request
    ): JsonResponse {
        
        $role = $this->roleRepository->findOneBy(['id' => $id]);
        if ( NULL == $role ) {
            return $usefulToolsHelper->generate_answer('', 'Role not found', 'ERROR_UPDATE_ROLE_1', 404);
        }

        if (!empty($request->request->get('name'))) {
            $sameNameRole = $this->roleRepository->searchOtherRoleWithSameName(
                $request->request->get('name'),
                $id
            );
            
            if ( NULL !== $sameNameRole && count($sameNameRole) > 0 ) {
                return $usefulToolsHelper->generate_answer('', 'Role with the same name already exists', 'ERROR_UPDATE_ROLE_2', 404);
            }
            $role->setName($request->request->get('name'));
        }

        if (!empty($request->request->get('permissions'))) {
            $arr = json_decode($request->request->get('permissions'), true);
            if (null === $arr) {
                return $usefulToolsHelper->generate_answer('', 'Cannot parse JSON string passed in the \'permissions\' param', 'ERROR_UPDATE_ROLE_3', 404);
            }
            $role->setPermissions($arr);
        }

        $this->roleRepository->save($role);    

        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }

    /**
     * Remove role.
     *
     * @param UsefulToolsHelper      $usefulToolsHelper
     * @param User|null              $user
     * @param int                    $id
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/administrator/role/{id}', name: 'remove_role', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(
        path: '/api/administrator/role/{id}',
        summary: 'Delete a role based on the role id',
        security: [['Bearer']],
        tags: ['Administrators'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: "Role's ID",
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
    public function removeRole(
        UsefulToolsHelper $usefulToolsHelper,
        int $id
    ): JsonResponse {
        $role = $this->roleRepository->findOneBy(['id' => $id]);
        if ( NULL == $role ) {
            return $usefulToolsHelper->generate_answer('', 'Role not found', 'ERROR_REMOVE_ROLE_1', 404);
        }
        
        $this->roleRepository->remove($role);
        
        // Return JSON response
        return $usefulToolsHelper->generate_answer();
    }
}
