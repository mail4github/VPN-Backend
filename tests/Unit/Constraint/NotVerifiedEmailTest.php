<?php

declare(strict_types=1);

namespace App\Tests\Unit\Constraint;

use App\Constraint\NotVerifiedEmail;
use App\Validator\NotVerifiedEmailValidator;
use PHPUnit\Framework\TestCase;

final class NotVerifiedEmailTest extends TestCase
{
    protected NotVerifiedEmail $constraint;

    protected function setUp(): void
    {
        $this->constraint = new NotVerifiedEmail();
    }

    public function testConstraintHasCorrectValidator(): void
    {
        $correctValidator = NotVerifiedEmailValidator::class;

        $currentValidator = $this->constraint->validatedBy();

        $this->assertSame($correctValidator, $currentValidator);
    }

    public function testConstraintHasValidationMessage(): void
    {
        $expectedMessage = 'Email "{{ value }}" is already verified.';

        $propertyExists = property_exists($this->constraint, 'message');

        $this->assertSame($expectedMessage, $this->constraint->message);
        $this->assertTrue($propertyExists);
    }
}
