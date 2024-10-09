<?php

declare(strict_types=1);

namespace App\Request;

use App\Constraint\NotVerifiedEmail;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyEmailRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[NotVerifiedEmail]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 4, max: 4)]
        public string $code,
    ) {}
}
