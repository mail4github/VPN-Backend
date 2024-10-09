<?php

declare(strict_types=1);

namespace App\Constraint;

use App\Validator\UniqueFieldValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @property class-string            $entity
 * @property string                  $field
 * @property array<array-key, mixed> $extraConditions
 * @property string                  $message
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UniqueField extends Constraint
{
    /** @var array<array-key, mixed> $extraConditions */
    public array $extraConditions = [];
    public string $message = 'The value "{{ value }}" is already in use.';
    public string $entity;
    public string $field;

    public function getRequiredOptions(): array
    {
        return ['entity', 'field'];
    }

    public function validatedBy(): string
    {
        return UniqueFieldValidator::class;
    }
}
