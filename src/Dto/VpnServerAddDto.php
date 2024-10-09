<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class VpnServerAddDto
{
    #[Assert\NotBlank(message: 'The value for \'ip\' should not be blank')]
    #[Assert\Ip(message: 'The value for \'ip\' must be a valid IP address')]
    public ?string $ip;

    #[Assert\NotBlank(message: 'The value for \'user_name\' should not be blank')]
    #[Assert\Length(min: 3, max: 128, minMessage: 'user_name too short', maxMessage: 'user_name too big')]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9]{3,128}+$/', message: 'The value for \'user_name\' must obey the regex pattern: {{ pattern }}')]
    public ?string $user_name;

    #[Assert\NotBlank(message: 'The value for \'password\' should not be blank')]
    #[Assert\Length(min: 6, max: 128, minMessage: 'password too short', maxMessage: 'password too big')]
    public ?string $password;

    #[Assert\NotBlank(message: 'The value for \'wallet_address\' should not be blank')]
    #[Assert\Regex(pattern: '/^0x[0-9a-zA-Z]{40,42}+$/', message: 'The value for \'wallet_address\' must obey the regex pattern: {{ pattern }}')]
    public ?string $wallet_address;

    public readonly bool $for_free;

    public readonly int $connection_quality;

    public readonly float $service_commission;

    public readonly int $maximum_active_connections;

    public readonly string $protocol;

    public readonly bool $residential_ip;

    public readonly bool $traffic_vs_period;

    public readonly array $test_packages;

    public readonly array $paid_packages;

    public function __construct(
        ?string $ip = null,
        ?string $user_name = null,
        ?string $password = null,
        ?string $wallet_address = null,
        bool $for_free = true,
        int $connection_quality = 0,
        float $service_commission = 0,
        int $maximum_active_connections = 0,
        string $protocol = 'WireGuard',
        bool $residential_ip = false,
        bool $traffic_vs_period = true,
        string $test_packages = '[]',
        string $paid_packages = '[]'
    ) {
        $this->ip = $ip;
        $this->user_name = $user_name;
        $this->password = $password;
        $this->wallet_address = $wallet_address;
        $this->for_free = $for_free;
        $this->connection_quality = $connection_quality;
        $this->service_commission = $service_commission;
        $this->maximum_active_connections = $maximum_active_connections;
        $this->protocol = $protocol;
        $this->residential_ip = $residential_ip;
        $this->traffic_vs_period = $traffic_vs_period;

        $arr = json_decode($test_packages, true);
        if (null === $arr) {
            throw new \Exception('Cannot parse JSON string passed in the \'test_packages\' param');
        }
        $this->test_packages = $arr;

        $arr = json_decode($paid_packages, true);
        if (null === $arr) {
            throw new \Exception('Cannot parse JSON string passed in the \'paid_packages\' param');
        }
        $this->paid_packages = $arr;
    }
}
