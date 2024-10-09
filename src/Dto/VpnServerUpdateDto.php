<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class VpnServerUpdateDto
{
    #[Assert\Regex(pattern: '/^[A-Z]{2,2}+$/')]
    public ?string $country = null;

    #[Assert\Ip]
    public ?string $ip = null;

    #[Assert\Length(min: 3, max: 128, minMessage: 'user_name too short', maxMessage: 'user_name too big')]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9]{3,128}+$/')]
    public ?string $user_name = null;

    #[Assert\Length(min: 6, max: 128, minMessage: 'password too short', maxMessage: 'password too big')]
    public ?string $password = null;

    #[Assert\Regex(pattern: '/^0x[0-9a-zA-Z]{40,42}+$/')]
    public ?string $wallet_address = null;

    #[Assert\Positive]
    public ?int $created_by = null;

    public ?bool $for_free = null;

    public ?int $connection_quality = null;

    public ?float $service_commission = null;

    public ?int $maximum_active_connections = null;

    public ?string $protocol = null;

    public ?bool $residential_ip = null;

    public ?bool $traffic_vs_period = null;

    public ?string $test_packages = null;

    public ?string $paid_packages = null;

    public function __construct(
        ?string $country = null,
        ?string $ip = null,
        ?string $user_name = null,
        ?string $password = null,
        ?string $wallet_address = null,
        ?int $created_by = null,
        ?bool $for_free = null,
        ?int $connection_quality = null,
        ?float $service_commission = null,
        ?int $maximum_active_connections = null,
        ?string $protocol = null,
        ?bool $residential_ip = null,
        ?bool $traffic_vs_period = null,
        ?string $test_packages = null,
        ?string $paid_packages = null
    ) {
        $this->country = $country;

        $this->ip = $ip;
        $this->user_name = $user_name;
        $this->password = $password;
        $this->wallet_address = $wallet_address;
        $this->created_by = $created_by;
        $this->for_free = $for_free;
        $this->connection_quality = $connection_quality;
        $this->service_commission = $service_commission;
        $this->maximum_active_connections = $maximum_active_connections;
        $this->protocol = $protocol;
        $this->residential_ip = $residential_ip;
        $this->traffic_vs_period = $traffic_vs_period;
        $this->test_packages = $test_packages;
        $this->paid_packages = $paid_packages;
    }
}
