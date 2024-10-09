<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\RecoveryCode;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;

final class RecoveryCodeManager implements BackupCodeManagerInterface
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {}

    public function issueRecoveryCodesForUser(User $user, int $quantity = 6): void
    {
        for ($i = 0; $i < $quantity; ++$i) {
            $code = $this->generate();
            $user->addRecoveryCode($code);
        }
    }

    public function generate(): RecoveryCode
    {
        $code = new RecoveryCode();
        $codeValue = bin2hex(random_bytes(4));
        $code->setCode($codeValue);

        return $code;
    }

    public function validate(User $user, string $code): bool
    {
        $recoveryCode = $this->getRecoveryCode($code);
        if (null === $recoveryCode) {
            return false;
        }

        return $user->getRecoveryCodes()->contains($recoveryCode);
    }

    public function isBackupCode(object $user, string $code): bool
    {
        if ($user instanceof User) {
            return $this->validate($user, $code);
        }

        return false;
    }

    public function invalidateBackupCode(object $user, string $code): void
    {
        if ($user instanceof User) {
            $recoveryCode = $this->getRecoveryCode($code);
            if (null !== $recoveryCode && $user->getRecoveryCodes()->contains($recoveryCode)) {
                $user->removeRecoveryCode($recoveryCode);
            }

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }
    }

    private function getRecoveryCode(string $code): ?RecoveryCode
    {
        $entityManager = $this->doctrine->getManager();
        $codesRepository = $entityManager->getRepository(RecoveryCode::class);

        return $codesRepository->findOneBy(['code' => $code]);
    }
}
