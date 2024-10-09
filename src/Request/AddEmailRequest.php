<?php

declare(strict_types=1);

namespace App\Request;

use App\Constraint\AvailableEmail;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddEmailRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[AvailableEmail]
        public string $email,
    ) {}
}
