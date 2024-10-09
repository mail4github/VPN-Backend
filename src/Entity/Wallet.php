<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(
    name: 'wallet',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'wallet_uniq_user', columns: ['user_id', 'address']),
    ]
)]
#[ORM\Entity]
class Wallet
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['default' => '1'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'wallet_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = 1;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    private int $userId;

    #[ORM\Column(
        name: 'address',
        type: 'text',
        nullable: false,
        options: ['comment' => 'Address of the user wallet']
    )]
    private string $address;

    #[ORM\Column(
        name: 'active',
        type: 'boolean',
        nullable: true,
        options: ['default' => '0', 'comment' => 'If true then this wallet is active wallet']
    )]
    private ?bool $active = true;

    #[ORM\Column(name: 'name', type: 'text', nullable: false)]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
