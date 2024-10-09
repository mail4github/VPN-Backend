<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionsGetDto
{
    #[Assert\Regex(pattern: '/^created$|^tr_type$|^amount$|^currency$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public ?int $user_id = null;

    public ?int $server_id = null;

    #[Assert\Regex(pattern: '/^test_traffic$|^test_period$|^traffic$|^period$/')]
    public ?string $connection_type = null;

    public ?int $offset = null; // Start rows from `offset`

    public ?int $limit = null; // Return the `limit` rows maximum

    public function __construct(
        string $sort_by = 'created',
        string $sort_order = 'desc',
        ?int $user_id = null,
        ?int $server_id = null,
        string $connection_type = '',
        ?int $offset = 0,
        int $limit = 24
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->user_id = $user_id;
        $this->server_id = $server_id;
        $this->connection_type = $connection_type;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
