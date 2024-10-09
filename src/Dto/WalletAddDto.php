<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class WalletAddDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^0x[0-9a-zA-Z]{40,42}+$/')]
    public ?string $address = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?bool $active = null;

    public function __construct(
        ?string $address,
        ?string $name,
        bool $active = true
    ) {
        $this->address = $address;
        $this->name = $name;
        $this->active = $active;
    }
}
