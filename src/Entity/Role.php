<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role.
 */
#[ORM\Entity]
#[ORM\Table(name: 'role', uniqueConstraints: [
    new ORM\UniqueConstraint(
        name: 'role_uniq_name',
        columns: ['name']
    ),
])]
class Role
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
        sequenceName: 'role_id_seq',
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
        name: 'modified',
        type: 'datetime',
        nullable: false
    )]
    private \DateTimeInterface $modified;

    #[ORM\Column(
        name: 'name',
        type: 'string',
        length: 64,
        nullable: false,
        options: ['comment' => 'Name of this role']
    )]
    private string $name;

    #[ORM\Column(
        name: 'permissions',
        type: 'text',
        nullable: false,
        options: [
            'comment' => 'a JSON text with array of permissions. Example: {"name": "read_users_list","read_only": true,"full_control": true}',
        ]
    )]
    private ?string $permissions = '[]';

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

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): static
    {
        $this->modified = $modified;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getPermissions(): array
    {
        return json_decode($this->permissions, true);
    }

    public function setPermissions(array $permissions): static
    {
        $this->permissions = json_encode($permissions);

        $this->setModified(new \DateTime());

        return $this;
    }
}
