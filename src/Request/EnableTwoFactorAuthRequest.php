<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EnableTwoFactorAuthRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $_auth_code,
    ) {}
}
