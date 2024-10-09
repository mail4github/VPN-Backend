<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Administrator.
 */
#[ORM\Entity]
#[ORM\Table(name: 'administrator', uniqueConstraints: [
    new ORM\UniqueConstraint(
        name: 'administrator_uniq_login',
        columns: ['login']
    ),
])]
class Administrator
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
        sequenceName: 'administrator_id_seq',
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
        name: 'last_login',
        type: 'datetime',
        nullable: true
    )]
    private \DateTimeInterface $lastLogin;

    #[ORM\Column(
        name: 'login',
        type: 'string',
        length: 64,
        nullable: false,
        options: ['comment' => 'Login name of this administrator']
    )]
    private ?string $login;

    #[ORM\Column(
        name: 'description',
        type: 'string',
        length: 256,
        nullable: true,
        options: ['default' => '', 'comment' => 'Description of this administrator']
    )]
    private ?string $description = '';

    #[ORM\Column(
        name: 'pgp_public_key',
        type: 'string',
        length: 10240,
        nullable: false,
        options: ['comment' => 'A string with PHP public key of this administrator. Is using to login']
    )]
    private ?string $pgpPublicKey;

    #[ORM\Column(
        name: 'superadmin',
        type: 'boolean',
        nullable: true,
        options: ['default' => false, 'comment' => 'If this value is true then this admin can manage other admins']
    )]
    private ?bool $superadmin = false;

    #[ORM\Column(
        name: 'blocked',
        type: 'boolean',
        nullable: true,
        options: ['default' => false, 'comment' => 'If this value is true then this admin is disabled']
    )]
    private ?bool $blocked = false;

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

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getPgpPublicKey(): ?string
    {
        return $this->pgpPublicKey;
    }

    public function setPgpPublicKey(string $pgpPublicKey): static
    {
        $this->pgpPublicKey = $pgpPublicKey;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function isSuperadmin(): ?bool
    {
        return $this->superadmin;
    }

    public function setSuperadmin(?bool $superadmin): static
    {
        $this->superadmin = $superadmin;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(?bool $blocked): static
    {
        $this->blocked = $blocked;

        $this->setModified(new \DateTime());

        return $this;
    }
}
