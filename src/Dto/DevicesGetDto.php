<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DevicesGetDto
{
    #[Assert\Regex(pattern: '/^ip$|^name$|^connected$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public function __construct(
        string $sort_by = 'connected',
        string $sort_order = 'desc'
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
    }
}
