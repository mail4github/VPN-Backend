<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EmailVerificationCode;
use App\Entity\User;
use App\Exception\VerifyEmailException;
use App\Repository\EmailVerificationCodeRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

final readonly class EmailVerificationService
{
    private const TEMPLATE = 'confirmation_email.html.twig';

    public function __construct(
        private UserRepository $userRepository,
        private EmailVerificationCodeRepository $codeRepository,
        private MailerInterface $mailer,
        private int $codeTtl,
        private string $from,
    ) {}

    /**
     * @throws VerifyEmailException
     * @throws TransportExceptionInterface
     */
    public function sendVerificationEmail(User $to): void
    {
        $verificationCode = $this->prepareVerificationCode($to);
        $email = $this->prepareVerificationEmail($to, $verificationCode);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailException
     */
    public function verifyEmail(string $code, User $user): void
    {
        $persistedCode = $this->getCodeRecord($code, $user);

        $user->setIsEmailVerified();
        $this->updateUsersWithPendingVerification($user);

        $this->codeRepository->remove($persistedCode, true);
        $this->userRepository->save($user, true);
    }

    /**
     * @throws VerifyEmailException
     * @throws \Exception
     */
    private function prepareVerificationCode(User $user): EmailVerificationCode
    {
        if (null === $user->getEmail()) {
            throw new VerifyEmailException('User didn\'t save his email');
        }

        $code = new EmailVerificationCode();
        $code->setOwner($user);
        $code->setCode((string) random_int(1111, 9999));
        $code->setExpiresAt(new \DateTimeImmutable('+ '.$this->codeTtl.' seconds'));

        $this->codeRepository->save($code, true);

        return $code;
    }

    /**
     * @throws VerifyEmailException
     */
    private function prepareVerificationEmail(User $user, EmailVerificationCode $code): TemplatedEmail
    {
        try {
            if (null === ($address = $user->getEmail())) {
                throw new \LogicException('Cannot create TemplatedEmail without an email address');
            }

            $email = new TemplatedEmail();
            $email->from($this->from);
            $email->to($address);
            $email->htmlTemplate(self::TEMPLATE);
            $email->context([
                'verification_code' => $code,
            ]);

            return $email;
        } catch (\Exception) {
            throw new VerifyEmailException('Failed to create verification email');
        }
    }

    /**
     * @throws VerifyEmailException
     */
    private function getCodeRecord(string $code, User $user): EmailVerificationCode
    {
        $persistedCode = $this->codeRepository->findOneBy([
            'code' => $code,
            'owner' => $user,
        ]);
        // $persistedCode = $this->codeRepository->findByUserAndValue($user, $code);

        if (null === $persistedCode) {
            throw new VerifyEmailException('Code not found');
        }

        if ($persistedCode->isExpired()) {
            throw new VerifyEmailException('Code has expired');
        }

        return $persistedCode;
    }

    private function updateUsersWithPendingVerification(User $verifiedUser): void
    {
        /**
         * @var Collection<int, User> $users
         */
        $users = $this->userRepository->findAll();
        if (!$users instanceof Collection || $users->isEmpty()) {
            return;
        }

        $users->filter(fn (User $user) => $user->getId() !== $verifiedUser->getId())
            ->map(function (User $user) {
                $user->setEmail(null);
                $user->setIsEmailVerified(false);
                $this->userRepository->save($user, true);
            });
    }
}
