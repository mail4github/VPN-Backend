<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class VpnConnectionsGetDto
{
    #[Assert\Regex(pattern: '/^created$|^ip$|^country$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public ?int $user_id = null;

    public ?int $offset = null; // Start rows from `offset`

    public ?int $limit = null; // Return the `limit` rows maximum

    public function __construct(
        string $sort_by = 'created',
        string $sort_order = 'desc',
        int $user_id = 0,
        ?int $offset = 0,
        int $limit = 24
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->user_id = $user_id;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
