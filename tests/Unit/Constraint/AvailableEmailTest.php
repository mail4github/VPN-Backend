<?php

declare(strict_types=1);

namespace App\Tests\Unit\Constraint;

use App\Constraint\AvailableEmail;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class AvailableEmailTest extends TestCase
{
    protected AvailableEmail $constraint;

    protected function setUp(): void
    {
        $this->constraint = new AvailableEmail();
    }

    public function testConstraintHasValidationMessage(): void
    {
        $expectedMessage = 'Email "{{ value }}" is already in use.';
        $messagePropertyExists = property_exists($this->constraint, 'message');

        $this->assertSame($expectedMessage, $this->constraint->message);
        $this->assertTrue($messagePropertyExists);
    }

    public function testConstraintHasConfiguredEntity(): void
    {
        $expectedEntity = User::class;
        $configuredEntity = $this->constraint->entity;

        $this->assertSame($expectedEntity, $configuredEntity);
    }

    public function testConstraintHasConfiguredField(): void
    {
        $expectedField = 'email';
        $configuredField = $this->constraint->field;

        $this->assertSame($expectedField, $configuredField);
    }

    public function testConstraintAppliesOnlyToVerifiedEmails(): void
    {
        $requiredConditions = ['isEmailVerified' => true];
        $configuredConditions = $this->constraint->extraConditions;

        $this->assertSame($requiredConditions, $configuredConditions);
    }
}
