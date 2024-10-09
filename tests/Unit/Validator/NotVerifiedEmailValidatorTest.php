<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Constraint\NotVerifiedEmail;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Validator\NotVerifiedEmailValidator;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class NotVerifiedEmailValidatorTest extends ConstraintValidatorTestCase
{
    private const VERIFIED_EMAIL = 'verified@email.org';
    private const UNVERIFIED_EMAIL = 'unverified@email.org';
    private const NOT_FOUND_EMAIL = 'notfound@email.org';

    public function testVerifiedEmailRaisesViolation(): void
    {
        $constraint = new NotVerifiedEmail();

        $this->validator->validate(self::VERIFIED_EMAIL, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', self::VERIFIED_EMAIL)
            ->assertRaised();
    }

    public function testUnverifiedEmailPassesValidation(): void
    {
        $constraint = new NotVerifiedEmail();

        $this->validator->validate(self::UNVERIFIED_EMAIL, $constraint);

        $this->assertNoViolation();
    }

    public function testValidatorThrowsNotFoundException(): void
    {
        $constraint = new NotVerifiedEmail();

        $this->expectException(EntityNotFoundException::class);
        $this->validator->validate(self::NOT_FOUND_EMAIL, $constraint);
    }

    public function testValidatorThrowsExceptionOnInvalidConstraint(): void
    {
        $invalidConstraint = new NotCompromisedPassword();

        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate('...', $invalidConstraint);
    }

    public function testValidatorSkipsEmptyValue(): void
    {
        $constraint = new NotVerifiedEmail();

        $this->validator->validate('', $constraint);
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $verifiedEmailUser = (new User())->setIsEmailVerified();
        $unverifiedEmailUser = (new User())->setIsEmailVerified(false);

        $repository = $this->createMock(UserRepository::class);

        $repository->expects($this->any())
            ->method('findOneBy')
            ->willReturnMap(
                [
                    [['email' => self::VERIFIED_EMAIL], null, $verifiedEmailUser],
                    [['email' => self::UNVERIFIED_EMAIL], null, $unverifiedEmailUser],
                    [['email' => self::NOT_FOUND_EMAIL], null, null],
                ]
            );

        return new NotVerifiedEmailValidator($repository);
    }
}
