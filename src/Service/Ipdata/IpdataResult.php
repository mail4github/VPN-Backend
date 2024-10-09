<?php

declare(strict_types=1);

namespace App\Service\Ipdata;

use Symfony\Component\Serializer\Annotation\SerializedName;

class IpdataResult implements IpdataResultInterface
{
    public function __construct(
        public ?string $ip,
        public ?string $city,
        public ?string $region,
        #[SerializedName('region_code')]
        public ?string $regionCode,
        #[SerializedName('country_name')]
        public ?string $countryName,
        #[SerializedName('country_code')]
        public ?string $countryCode,
        public ?float $latitude,
        public ?float $longitude,
    ) {}

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
