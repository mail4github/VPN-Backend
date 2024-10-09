<?php

declare(strict_types=1);

namespace App\Validator;

use App\Constraint\NotVerifiedEmail;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @Annotation
 */
final class NotVerifiedEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    /**
     * @throws EntityNotFoundException
     * @throws UnexpectedValueException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotVerifiedEmail) {
            throw new UnexpectedValueException($constraint, NotVerifiedEmail::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->repository->findOneBy(['email' => $value]);
        if (null === $user) {
            /** @var array<array-key, string> $identifier */
            $identifier = ['email' => $value];
            throw EntityNotFoundException::fromClassNameAndIdentifier(User::class, $identifier);
        }

        if ($user->getIsEmailVerified()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value)
                ->addViolation();
        }
    }
}
