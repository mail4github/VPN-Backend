<?php

declare(strict_types=1);

namespace App\Constraint;

use App\Entity\User;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AvailableEmail extends UniqueField
{
    public string $entity = User::class;
    public string $field = 'email';

    /**
     * @var array<array-key, mixed>
     */
    public array $extraConditions = [
        'isEmailVerified' => true,
    ];

    public string $message = 'Email "{{ value }}" is already in use.';

    public function getRequiredOptions(): array
    {
        return [];
    }
}
