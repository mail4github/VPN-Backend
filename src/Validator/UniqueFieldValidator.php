<?php

declare(strict_types=1);

namespace App\Validator;

use App\Constraint\UniqueField;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @Annotation
 */
final class UniqueFieldValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof UniqueField) {
            throw new UnexpectedValueException($constraint, UniqueField::class);
        }

        try {
            $repository = $this->doctrine->getRepository($constraint->entity);
        } catch (\Exception) {
            throw new UnexpectedValueException($constraint->entity, Entity::class);
        }

        /** @var array<string, mixed> $conditions */
        $conditions = array_merge([$constraint->field => $value], $constraint->extraConditions);
        $searchResults = $repository->findBy($conditions);

        if (\count($searchResults) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value)
                ->addViolation();
        }
    }
}
