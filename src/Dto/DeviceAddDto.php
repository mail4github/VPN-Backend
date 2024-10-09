<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DeviceAddDto
{
    #[Assert\NotBlank]
    #[Assert\Ip]
    public readonly string $ip;

    #[Assert\NotBlank]
    public readonly string $name;

    public readonly bool $active;

    public readonly string $fingerprint;

    #[Assert\Regex(pattern: '/^[A-Z]{2,2}+$/')]
    public readonly string $country;

    #[Assert\NotBlank]
    public readonly string $type;

    public function __construct(
        string $ip,
        string $name,
        bool $active = true,
        string $fingerprint = '',
        string $country = '',
        string $type = ''
    ) {
        $this->ip = $ip;
        $this->name = $name;
        $this->active = $active;
        $this->fingerprint = $fingerprint;
        $this->country = $country;
        $this->type = $type;
    }
}
