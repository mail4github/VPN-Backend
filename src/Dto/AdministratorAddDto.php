<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AdministratorAddDto
{
    #[Assert\NotBlank(message: 'The value for \'login\' should not be blank')]
    public ?string $login;

    public ?string $description = null;

    #[Assert\NotBlank(message: 'The value for \'pgp_public_key\' should not be blank')]
    public ?string $pgp_public_key;

    public ?bool $superadmin = null;

    public ?bool $blocked = null;

    public ?array $roles = null;

    public function __construct(
        ?string $login = null,
        ?string $pgp_public_key = null,
        ?string $description = null,
        ?bool $superadmin = null,
        ?bool $blocked = null,
        ?string $roles = null
    ) {
        $this->login = $login;
        $this->description = $description;
        $this->pgp_public_key = $pgp_public_key;
        $this->superadmin = $superadmin;
        $this->blocked = $blocked;
        $arr = json_decode($roles, true);
        if (null === $arr) {
            throw new \Exception('Cannot parse JSON string passed in the \'roles\' param');
        }
        $this->roles = $arr;
    }
}
