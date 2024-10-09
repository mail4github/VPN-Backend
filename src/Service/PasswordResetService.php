<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ResetPasswordCode;
use App\Entity\User;
use App\Repository\ResetPasswordCodeRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final readonly class PasswordResetService
{
    private const TEMPLATE = 'reset_password_email.html.twig';

    public function __construct(
        private ResetPasswordCodeRepository $codeRepository,
        private MailerInterface $mailer,
        private int $codeTtl,
        private string $from,
    ) {}

    public function sendPasswordResetCode(User $to): void
    {
        $code = $this->prepareResetCode($to);
        $email = $this->prepareResetPasswordEmail($to, $code);

        $this->mailer->send($email);

        $this->invalidatePreviousResetCodes($to, $code);
    }

    public function verifyCode(User $user, string $code): bool
    {
        $codeInstance = $this->codeRepository->findOneBy(['code' => $code]);
        if (null === $codeInstance || $codeInstance->isExpired()) {
            return false;
        }

        return $user->getResetPasswordCodes()->contains($codeInstance);
    }

    public function addToken(string $code): string
    {
        $codeInstance = $this->codeRepository->findOneBy(['code' => $code]);
        if (null === $codeInstance) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(ResetPasswordCode::class, ['code' => $code]);
        }

        $token = uniqid();
        $codeInstance->setToken($token);
        $this->codeRepository->save($codeInstance, true);

        return $token;
    }

    public function getUserByToken(string $token): ?User
    {
        $code = $this->codeRepository->findOneBy(['token' => $token]);
        if ($code) {
            return $code->getOwner();
        }

        return null;
    }

    public function invalidateCode(string $token): void
    {
        $code = $this->codeRepository->findOneBy(['token' => $token]);
        if ($code) {
            $this->codeRepository->remove($code, true);
        }
    }

    private function prepareResetCode(User $user): ResetPasswordCode
    {
        $code = new ResetPasswordCode();
        $code->setOwner($user);
        $code->setCode((string) random_int(1111, 9999));
        $code->setExpiresAt(new \DateTimeImmutable('+ '.$this->codeTtl.' seconds'));

        $this->codeRepository->save($code, true);

        return $code;
    }

    private function prepareResetPasswordEmail(User $user, ResetPasswordCode $code): TemplatedEmail
    {
        if (null === ($address = $user->getEmail())) {
            throw new \LogicException('Cannot create TemplatedEmail without an email address');
        }

        $email = new TemplatedEmail();
        $email->from($this->from);
        $email->to($address);
        $email->htmlTemplate(self::TEMPLATE);
        $email->context([
            'reset_password_code' => $code,
        ]);

        return $email;
    }

    private function invalidatePreviousResetCodes(User $user, ResetPasswordCode $lastResetCode): void
    {
        $previousCodes = $user->getResetPasswordCodes()
            ->filter(fn (ResetPasswordCode $code) => $code->getId() !== $lastResetCode->getId());

        $previousCodes->map(function (ResetPasswordCode $code) use ($user) {
            $user->removeResetPasswordCode($code);
            $this->codeRepository->remove($code, true);
        });
    }
}
