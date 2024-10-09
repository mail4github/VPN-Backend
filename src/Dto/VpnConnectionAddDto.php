<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class VpnConnectionAddDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $user_id,
        #[Assert\NotBlank]
        #[Assert\Ip]
        public string $ip,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[A-Z]{2,2}+$/')]
        public string $country,
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $server_id,
        #[Assert\NotBlank]
        public string $protocol,
        public float $duration = 0,
        public float $total_traffic = 0,
        public string $description = ''
    ) {}
}
