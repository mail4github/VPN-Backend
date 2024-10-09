<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class WalletsGetDto
{
    #[Assert\Regex(pattern: '/^address$|^name$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public function __construct(
        string $sort_by = 'address',
        string $sort_order = 'asc'
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
    }
}
