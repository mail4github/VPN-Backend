<?php

declare(strict_types=1);

namespace App\Request;

use App\Constraint as CustomAssert;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegistrationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(min: 6, max: 16)]
        #[CustomAssert\UniqueField(['entity' => User::class, 'field' => 'login'])]
        public ?string $login,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(min: 8)]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Z])[a-zA-Z0-9!%$#&_-]+$/',
            message: 'Password must contains latin letters, at least one uppercase, symbols.'
        )]
        public ?string $password,
        #[Assert\Email]
        #[Assert\Type('string')]
        #[CustomAssert\AvailableEmail]
        public ?string $email = null,
        #[Assert\Type('bool')]
        public bool $enable2fa = false,
    ) {}
}
