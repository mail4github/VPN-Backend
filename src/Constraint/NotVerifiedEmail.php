<?php

declare(strict_types=1);

namespace App\Constraint;

use App\Validator\NotVerifiedEmailValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotVerifiedEmail extends Constraint
{
    public string $message = 'Email "{{ value }}" is already verified.';

    public function validatedBy(): string
    {
        return NotVerifiedEmailValidator::class;
    }
}
