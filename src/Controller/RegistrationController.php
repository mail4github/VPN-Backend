<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Request\RegistrationRequest;
use App\Security\RecoveryCodeManager;
use App\Security\Token\AuthenticationTokenGeneratorInterface;
use App\Service\EmailVerificationService;
use Doctrine\Persistence\ManagerRegistry;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly RecoveryCodeManager $recoveryCodeManager,
        private readonly ManagerRegistry $doctrine,
        private readonly EmailVerificationService $emailVerificationService,
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly AuthenticationTokenGeneratorInterface $authenticationTokenGenerator
    ) {}

    #[Route('/api/register', name: 'app_registration', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/register',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password', 'enable2fa'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'username'),
                    new OA\Property(property: 'email', type: 'string', example: 'username@vpn.org'),
                    new OA\Property(property: 'password', type: 'string', example: 'nmc94358nd'),
                    new OA\Property(property: 'enable2fa', type: 'string', example: 'true'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: '200', description: 'User created'),
            new OA\Response(response: '400', description: 'Bad request'),
            new OA\Response(response: '422', description: 'Invalid data or constraint violation'),
        ]
    )]
    public function register(#[MapRequestPayload] RegistrationRequest $request): Response
    {
        $user = $this->prepareUser($request);

        if ($request->enable2fa) {
            $this->configureTwoFactorAuthentication($user);
        }

        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $user->eraseCredentials();

            if ($user->getEmail()) {
                $this->emailVerificationService->sendVerificationEmail($user);
            }

            $this->security->login($user, 'json_login');

            $token = $this->authenticationTokenGenerator->generate($user, $request->enable2fa);

            return $this->json([
                'token' => $token,
            ]);
        } catch (TransportExceptionInterface $mailException) {
            $user->setEmail(null);
            $user->setIsEmailVerified(false);

            $this->logger->error($mailException);
            $this->security->login($user, 'json_login');

            $token = $this->authenticationTokenGenerator->generate($user, $request->enable2fa);

            return $this->json([
                'token' => $token,
            ]);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException('Failed to create new User', $exception);
        }
    }

    private function prepareUser(RegistrationRequest $request): User
    {
        $user = new User();
        $user->setLogin($request->login);
        $user->setPlainPassword($request->password);
        $user->setEmail($request->email);
        $user->setIsEmailVerified(false);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($hashedPassword);

        return $user;
    }

    private function configureTwoFactorAuthentication(User &$user): void
    {
        $secret = $this->googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($secret);
        $user->setIsTwoFactorAuthEnabled(true);

        $this->recoveryCodeManager->issueRecoveryCodesForUser($user);
    }
}
