<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adminrole.
 */
#[ORM\Entity]
#[ORM\Table(name: 'adminrole', uniqueConstraints: [
    new ORM\UniqueConstraint(
        name: 'adminrole_uniq',
        columns: ['admin_id', 'role_id']
    ),
])]
class Adminrole
{
    #[ORM\Column(
        name: 'id',
        type: 'integer',
        nullable: false,
        options: ['default' => '1']
    )]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(
        sequenceName: 'adminrole_id_seq',
        allocationSize: 1,
        initialValue: 1
    )]
    private int $id;

    #[ORM\Column(
        name: 'created',
        type: 'datetime',
        nullable: false
    )]
    private \DateTimeInterface $created;

    #[ORM\Column(
        name: 'admin_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'The administrator id in the administrator table']
    )]
    private ?int $adminId;

    #[ORM\Column(
        name: 'role_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'The role id in the role table']
    )]
    private ?int $roleId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getAdminId(): ?int
    {
        return $this->adminId;
    }

    public function setAdminId(int $adminId): static
    {
        $this->adminId = $adminId;

        return $this;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): static
    {
        $this->roleId = $roleId;

        return $this;
    }
}
