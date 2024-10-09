<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Concern\JsonResponseTrait;
use App\Dto\UserUpdateDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UsefulToolsHelper;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    #[Route('/api/profile', name: 'app_profile', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/profile',
        summary: 'Fetch current user info',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Array of User properties'
            ),
            new OA\Response(
                response: 401,
                description: 'User is not authenticated'
            ),
        ]
    )]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->unauthorized();
        }

        return $this->json($user);
    }

    /**
     * Update user profile.
     *
     * @param UsefulToolsHelper $usefulToolsHelper
     * @param User|null         $user
     * @param UserUpdateDto     $dto
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/profile/update', name: 'update_user_profile', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/profile/update',
        summary: "Update user's profile",
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'login',
                description: "User's login name",
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'alex')
            ),
            new OA\Parameter(
                name: 'picture',
                description: "User's picture in BASE64 format like `data:image/png;base64, iVBORw0KG...`",
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'data:image/png;base64, iVBORw0KG...')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Profile updated'),
            new OA\Response(response: 401, description: 'User is not authenticated'),
        ]
    )]
    public function updateUserProfile(
        #[CurrentUser]
        ?User $user,
        #[MapRequestPayload]
        UserUpdateDto $dto
    ): JsonResponse {
        // $user = $this->userRepository->findOneBy(['id' => 1]);

        if (null === $user) {
            return $this->unauthorized();
        }

        if (null !== $dto->login) {
            $user->setLogin($dto->login);
        }

        if (null !== $dto->picture) {
            $user->setPicture($dto->picture); // User's picture in BASE64 format like: `data:image/png;base64, iVBORw0KG...`
        }

        $this->userRepository->save($user, true);

        return $this->success('Profile updated');
    }

    /**
     * Get account balance.
     *
     * @param UsefulToolsHelper $usefulToolsHelper
     * @param User|null         $user
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/profile/balance', name: 'get_account_balance', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/profile/balance',
        summary: 'Get account balance',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A record, which has a value called `results`, which contains an array with the user balances',
                content: [
                    new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: 'account_balance',
                                type: 'number'
                            ),
                            new OA\Property(
                                property: 'on_hold',
                                type: 'number'
                            ),
                        ],
                        type: 'object'
                    ),
                ]
            ),
            new OA\Response(response: 401, description: 'User is not authenticated'),
        ]
    )]
    public function getAccountBalance(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user
    ): JsonResponse {
        if (null === $user) {
            return $this->unauthorized();
        }

        $data = [
            'account_balance' => 10.01, // must be coded in future
            'on_hold' => 1.02, // must be coded in future
        ];

        // Return JSON response
        return $usefulToolsHelper->generate_answer($data);
    }

    /**
     * Update user deactivate_sessions_after_days.
     *
     * @param UsefulToolsHelper $usefulToolsHelper
     * @param User|null         $user
     * @param Request           $request
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    #[Route('/api/profile/deactivate_sessions_after_days', name: 'update_user_deactivate_sessions_after_days', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/profile/deactivate_sessions_after_days',
        summary: 'Make user sessions not active after that number of days',
        tags: ['User Devices', 'Profile'],
        parameters: [
            new OA\Parameter(
                name: 'deactivate_sessions_after_days',
                description: 'Deactivate user sessions after this number of days',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 14)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful'),
            new OA\Response(response: 404, description: 'Invalid request parameters'),
        ]
    )]
    public function updateUserDeactivateSessionsAfterDays(
        UsefulToolsHelper $usefulToolsHelper,
        #[CurrentUser]
        ?User $user,
        Request $request
    ): JsonResponse {
        if (null === $user) {
            return $this->unauthorized();
        }
        if (empty($request->request->get('deactivate_sessions_after_days'))) {
            return $usefulToolsHelper->generate_answer('', 'You have to specify number of days in the deactivate_sessions_after_days parameter', 'ERROR_UPDATEUSERDEACTIVATESESSIONSAFTERDAYS_1', 404);
        }
        $user->setDeactivateSessionsAfterDays($request > request->get('deactivate_sessions_after_days')); // Make user sessions not active after that number of days
        $this->userRepository->save($user, true);

        return $this->success('DeactivateSessionsAfterDays updated');
    }
}
