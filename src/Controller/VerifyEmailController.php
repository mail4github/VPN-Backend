<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Concern\JsonResponseTrait;
use App\Entity\User;
use App\Exception\VerifyEmailException;
use App\Repository\UserRepository;
use App\Request\AddEmailRequest;
use App\Request\VerifyEmailRequest;
use App\Service\EmailVerificationService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class VerifyEmailController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(
        private readonly EmailVerificationService $verificationService,
        private readonly UserRepository $repository,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/api/add-email', name: 'app_add_email', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/add-email',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    schema: 'object',
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', example: 'username@vpn.org'),
                        new OA\Property(property: 'code', type: 'string',  example: '1234'),
                    ]
                ),
            ]
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Email verified'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 422, description: 'Invalid data or constraint violation'),
        ]
    )]
    public function send(#[CurrentUser] ?User $user, #[MapRequestPayload] AddEmailRequest $request): JsonResponse
    {
        if (null === $user) {
            return $this->unauthorized();
        }

        try {
            $user->setEmail($request->email);
            $user->setIsEmailVerified(false);

            $this->repository->save($user, true);
            $this->verificationService->sendVerificationEmail($user);

            return $this->success('Successfully sent email verification code');
        } catch (TransportExceptionInterface $mailerException) {
            $this->logger->error($mailerException);

            return $this->error('Failed to send code via email');
        } catch (\Exception $exception) {
            $this->logger->error($exception);

            return $this->error('Server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[OA\Post(
        path: '/api/verify-email',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                schema: 'object',
                required: ['email', 'code'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'username@vpn.org'),
                    new OA\Property(property: 'code', type: 'string', example: '1234'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: 'Email verified'),
            new OA\Response(response: '400', description: 'Bad request'),
            new OA\Response(response: '422', description: 'Invalid data or constraint violation'),
        ]
    )]
    #[Route('/api/verify-email', name: 'app_verify_email', methods: [Request::METHOD_POST])]
    public function verify(#[MapRequestPayload] VerifyEmailRequest $request): JsonResponse
    {
        $user = $this->repository->findOneBy(['email' => $request->email]);
        if (null === $user) {
            return $this->error('No user with this email', Response::HTTP_NOT_FOUND);
        }

        try {
            $this->verificationService->verifyEmail($request->code, $user);

            return $this->json($user);
        } catch (VerifyEmailException $exception) {
            return $this->error($exception->getMessage());
        } catch (\Exception $exception) {
            return $this->error(
                'Server error: '.$exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
