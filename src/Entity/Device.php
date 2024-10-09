<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Device.
 */
#[ORM\Entity]
#[ORM\Table(name: 'device', uniqueConstraints: [
    new ORM\UniqueConstraint(
        name: 'device_uniq_user',
        columns: ['user_id', 'ip', 'fingerprint']
    ),
])]
class Device
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
        sequenceName: 'device_id_seq',
        allocationSize: 1,
        initialValue: 1
    )]
    private int $id = 1;

    #[ORM\Column(
        name: 'user_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'ID in the User table']
    )]
    private int $userId;

    #[ORM\Column(
        name: 'ip',
        type: 'text',
        nullable: false,
        options: ['comment' => 'IP address of the user device']
    )]
    private string $ip;

    #[ORM\Column(
        name: 'active',
        type: 'boolean',
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'If true then this device is active device',
        ]
    )]
    private ?bool $active = true;

    #[ORM\Column(name: 'name', type: 'text', nullable: false)]
    private string $name;

    #[ORM\Column(
        name: 'fingerprint',
        type: 'text',
        nullable: false,
        options: ['comment' => 'Fingerprint of the user device']
    )]
    private string $fingerprint;

    #[ORM\Column(
        name: 'country',
        type: 'text',
        nullable: false,
        options: ['comment' => 'Country of the user device']
    )]
    private string $country;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTimeInterface $created;

    #[ORM\Column(name: 'modified', type: 'datetime', nullable: false)]
    private \DateTimeInterface $modified;

    #[ORM\Column(
        name: 'connected',
        type: 'datetime',
        nullable: false,
        options: ['comment' => 'date and time of last connection']
    )]
    private \DateTimeInterface $connected;

    #[ORM\Column(
        name: 'type',
        type: 'string',
        length: 20,
        nullable: true,
        options: ['comment' => 'Type of the device, like: phone, desktop, notepad etc.']
    )]
    private ?string $type;

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

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        $this->setModified(new \DateTime());

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

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): static
    {
        $this->fingerprint = $fingerprint;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        $this->setModified(new \DateTime());

        return $this;
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

    public function getConnected(): ?\DateTimeInterface
    {
        return $this->connected;
    }

    public function setConnected(\DateTimeInterface $connected): static
    {
        $this->connected = $connected;

        $this->setModified(new \DateTime());

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        $this->setModified(new \DateTime());

        return $this;
    }
}
