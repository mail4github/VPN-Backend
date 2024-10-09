<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RoleAddDto
{
    #[Assert\NotBlank(message: 'The value for \'name\' should not be blank')]
    public ?string $name;

    #[Assert\NotBlank(message: 'The value for \'permissions\' should not be blank')]
    public ?array $permissions;

    public function __construct(
        ?string $name = null,
        ?string $permissions = null
    ) {
        $this->name = $name;
        $arr = json_decode($permissions, true);
        if (null === $arr) {
            throw new \Exception('Cannot parse JSON string passed in the \'permissions\' param');
        }
        $this->permissions = $arr;
    }
}
