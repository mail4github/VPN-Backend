<?php

declare(strict_types=1);

namespace App\Tests\Unit\Constraint;

use App\Constraint\UniqueField;
use App\Validator\UniqueFieldValidator;
use PHPUnit\Framework\TestCase;

final class UniqueFieldTest extends TestCase
{
    protected UniqueField $constraint;

    protected function setUp(): void
    {
        $this->constraint = new UniqueField([
            'entity' => 'ValidEntity',
            'field' => 'field',
        ]);
    }

    public function testConstraintHasCorrectValidator(): void
    {
        $correctValidator = UniqueFieldValidator::class;

        $currentValidator = $this->constraint->validatedBy();

        $this->assertSame($correctValidator, $currentValidator);
    }

    public function testConstraintHasValidationMessage(): void
    {
        $expectedMessage = 'The value "{{ value }}" is already in use.';
        $messagePropertyExists = property_exists($this->constraint, 'message');

        $this->assertSame($expectedMessage, $this->constraint->message);
        $this->assertTrue($messagePropertyExists);
    }

    public function testConstraintRequiredOptions(): void
    {
        $expectedOptions = ['entity', 'field'];
        $constraintOptions = $this->constraint->getRequiredOptions();

        foreach ($expectedOptions as $option) {
            $this->assertContains($option, $constraintOptions);
            $this->assertTrue(property_exists($this->constraint, $option));
        }
    }
}
