<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AdministratorsGetDto
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^created$|^login$|^last_login$|^role$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public ?string $search = null; // Search for this string in login, description or in the role name

    public ?int $offset = null; // Start rows from `offset`

    public ?int $limit = null; // Return the `limit` rows maximum

    public function __construct(
        ?string $sort_by = null,
        string $sort_order = 'asc',
        ?string $search = '',
        ?int $offset = 0,
        int $limit = 24
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->search = $search;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
