<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token,
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        #[Assert\Regex(pattern: '/^(?=.*[A-Z])[a-zA-Z0-9!%$#&_-]+$/')]
        public string $password,
    ) {}
}
