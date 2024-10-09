<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UsersGetDto
{
    #[Assert\Regex(pattern: '/^login$|^email$|^owns_servers$/', message: 'The value for \'sort_by\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_by = null;

    #[Assert\Regex(pattern: '/^asc$|^desc$/i', message: 'The value for \'sort_order\' must obey the regex pattern: {{ pattern }}')]
    public ?string $sort_order = null;

    public ?string $login = null;

    public ?int $offset = null; // Start rows from `offset`

    public ?int $limit = null; // Return the `limit` rows maximum

    public function __construct(
        string $sort_by = 'login',
        string $sort_order = 'desc',
        string $login = '',
        ?int $offset = 0,
        int $limit = 24
    ) {
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->login = $login;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
