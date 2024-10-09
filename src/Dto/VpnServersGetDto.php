<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class VpnServersGetDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^all$|^subscribed$|^favorites$|^own$/')]
    public ?string $pick_out = null;

    public ?int $created_by = null;

    #[Assert\Regex(pattern: '/^connection_quality$|^created$|^price$|^user_name$|^country$|^ip$|^protocol$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public ?string $country = null;

    public ?bool $for_free = null;

    public ?bool $limited_time_rent_available = null;

    public ?bool $limited_traffic_rent_available = null;

    public ?string $protocol = null;

    public ?bool $residential_ip = null;

    public ?string $user_name = null;

    public ?string $ip_address = null;

    public ?int $offset = 0; // "offset" Skip the first `offset` rows

    public ?int $limit = 24; // "limit" Return the `limit` rows maximum

    public function __construct(
        string $pick_out = null,
        int $created_by = 0,
        string $sort_by = 'connection_quality',
        string $sort_order = 'desc',
        string $country = '',
        ?string $for_free = null,
        ?bool $limited_time_rent_available = null,
        ?bool $limited_traffic_rent_available = null,
        string $protocol = '',
        ?string $residential_ip = null,
        string $user_name = '',
        string $ip_address = '',
        int $offset = 0,
        int $limit = 24
    ) {
        $this->pick_out = $pick_out;
        $this->created_by = $created_by;
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->country = $country;
        if (null != $for_free) {
            $this->for_free = '1' == $for_free || 'true' == $for_free ? true : false;
        }
        $this->limited_time_rent_available = $limited_time_rent_available;
        $this->limited_traffic_rent_available = $limited_traffic_rent_available;
        $this->protocol = $protocol;
        if (null != $residential_ip) {
            $this->residential_ip = '1' == $residential_ip || 'true' == $residential_ip ? true : false;
        }
        $this->user_name = $user_name;
        $this->ip_address = $ip_address;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
