<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Constraint\UniqueField;
use App\Validator\UniqueFieldValidator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class UniqueFieldValidatorTest extends ConstraintValidatorTestCase
{
    public function testDuplicateValueRaisesViolation(): void
    {
        $constraint = new UniqueField([
            'entity' => 'ValidEntity',
            'field' => 'field',
        ]);

        $this->validator->validate('duplicate', $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'duplicate')
            ->assertRaised();
    }

    public function testUniqueValuePassesValidation(): void
    {
        $constraint = new UniqueField([
            'entity' => 'ValidEntity',
            'field' => 'field',
        ]);

        $this->validator->validate('unique', $constraint);

        $this->assertNoViolation();
    }

    public function testValidatorThrowsExceptionOnInvalidConstraint(): void
    {
        $invalidConstraint = new NotCompromisedPassword();

        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate('...', $invalidConstraint);
    }

    public function testValidatorThrowsExceptionOnInvalidEntity(): void
    {
        $constraint = new UniqueField([
            'entity' => 'InvalidEntity',
            'field' => 'field',
        ]);

        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate('unique', $constraint);
    }

    public function testValidatorSkipsEmptyValue(): void
    {
        $constraint = new UniqueField([
            'entity' => 'entity',
            'field' => 'field',
        ]);

        $this->validator->validate('', $constraint);
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->any())
            ->method('findBy')
            ->willReturnMap([
                [['field' => 'duplicate'], null, null, null, [new \stdClass()]],
                [['field' => 'unique'], null, null, null, []],
            ]);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($argument) use ($repository) {
                if ('ValidEntity' === $argument) {
                    return $repository;
                }

                throw new \Exception();
            });

        return new UniqueFieldValidator($doctrine);
    }
}
