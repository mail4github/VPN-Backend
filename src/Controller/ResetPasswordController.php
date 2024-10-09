<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Concern\JsonResponseTrait;
use App\Repository\UserRepository;
use App\Request\ResetPasswordRequest;
use App\Request\SendResetPasswordCodeRequest;
use App\Request\VerifyResetPasswordCodeRequest;
use App\Service\PasswordResetService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordResetService $passwordResetService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/api/reset-password-code', name: 'app_send_reset_password_code', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/reset-password-code',
        summary: 'Send a password reset code via email',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'login',
                description: 'User login (not email!)',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Email with code was sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'result',
                            type: 'string',
                            example: 'Successfully sent password reset code'
                        ),
                        new OA\Property(
                            property: 'address',
                            type: 'string',
                            example: 'user@email.org'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '400',
                description: 'User does not have verified email'
            ),
            new OA\Response(
                response: '404',
                description: 'User not found'
            ),
        ]
    )]
    public function send(#[MapQueryString] SendResetPasswordCodeRequest $request): Response
    {
        $user = $this->userRepository->findOneBy(['login' => $request->login]);
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$user->getIsEmailVerified()) {
            throw new BadRequestHttpException('User does not have verified email');
        }

        $this->passwordResetService->sendPasswordResetCode($user);

        return $this->json([
            'result' => 'Successfully sent password reset code',
            'address' => $user->getEmail(),
        ]);
    }

    #[Route('/api/verify-reset-code', name: 'app_check_reset_password_code', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/verify-reset-code',
        summary: 'Verify reset password code',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'code'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'code',
                        type: 'string'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Code verified and one-time token generated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'token',
                            description: 'One-time token to reset password',
                            type: 'string'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '401',
                description: 'Invalid code'
            ),
            new OA\Response(
                response: '404',
                description: 'User not found'
            ),
        ]
    )]
    public function verify(#[MapRequestPayload] VerifyResetPasswordCodeRequest $request): Response
    {
        $user = $this->userRepository->findOneBy(['login' => $request->login]);
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$this->passwordResetService->verifyCode($user, $request->code)) {
            throw new AccessDeniedHttpException('Invalid code');
        }

        $resetPasswordToken = $this->passwordResetService->addToken($request->code);

        return $this->json(['token' => $resetPasswordToken]);
    }

    #[Route('/api/reset-password', name: 'app_set_new_password', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/reset-password',
        summary: 'Update user password with one-time token',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['token', 'password'],
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Password updated'
            ),
            new OA\Response(
                response: '404',
                description: 'User not found'
            ),
        ]
    )]
    public function reset(#[MapRequestPayload] ResetPasswordRequest $request): Response
    {
        $user = $this->passwordResetService->getUserByToken($request->token);
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        $newPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($newPassword);
        $this->userRepository->save($user, true);

        $this->passwordResetService->invalidateCode($request->token);

        return $this->success('Password updated');
    }
}
