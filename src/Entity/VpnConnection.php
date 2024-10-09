<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VpnConnectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'vpn_connection')]
#[ORM\Entity(repositoryClass: VpnConnectionRepository::class)]
class VpnConnection
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['default' => '1'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(
        sequenceName: 'vpn_connection_id_seq',
        allocationSize: 1,
        initialValue: 1
    )]
    private int $id = 1;

    #[ORM\Column(
        name: 'user_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'User who has connected to this server. Id in the User table']
    )]
    private int $userId;

    #[ORM\Column(
        name: 'ip',
        type: 'string',
        length: 20,
        nullable: false,
        options: ['comment' => 'IP address of the connected user']
    )]
    private string $ip;

    #[ORM\Column(
        name: 'country',
        type: 'string',
        length: 3,
        nullable: true,
        options: ['default' => '', 'comment' => 'Country of the connected user']
    )]
    private string $country = '';

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTimeInterface $created;

    #[ORM\Column(name: 'modified', type: 'datetime', nullable: false)]
    private \DateTimeInterface $modified;

    #[ORM\Column(
        name: 'server_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'Id of VPN server']
    )]
    private int $serverId;

    #[ORM\Column(
        name: 'duration',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'The time that user spent on the server (in seconds, probably)',
        ]
    )]
    private ?float $duration = 0;

    #[ORM\Column(
        name: 'total_traffic',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'Total traffic that has been sent and received during the connection',
        ]
    )]
    private ?float $totalTraffic = 0;

    #[ORM\Column(
        name: 'description',
        type: 'string',
        length: 255,
        nullable: true
    )]
    private string $description = '';

    #[ORM\Column(
        name: 'protocol',
        type: 'string',
        length: 64,
        nullable: true,
        options: [
            'default' => 'WireGuard',
            'comment' => 'Possible values: WireGuard, OpenVPN (UDP), OpenVPN (TCP)',
        ]
    )]
    private ?string $protocol = 'WireGuard';

    #[ORM\Column(
        name: 'connection_type',
        type: 'string',
        length: 32,
        nullable: true,
        options: [
            'default' => 'traffic',
            'comment' => 'Type of connection like: test_traffic, test_period, traffic, period',
        ]
    )]
    private string $connectionType = '';

    #[ORM\Column(
        name: 'client_config',
        type: Types::TEXT,
        nullable: true,
        options: [
            'default' => null,
            'comment' => 'Encoded client configuration file',
        ]
    )]
    private ?string $clientConfig = null;

    #[ORM\Column(
        name: 'client_name',
        type: Types::STRING,
        nullable: true,
        options: [
            'default' => null,
            'comment' => 'VPN Client Name',
        ]
    )]
    private ?string $clientName = null;

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

    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(int $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(?float $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getTotalTraffic(): ?float
    {
        return $this->totalTraffic;
    }

    public function setTotalTraffic(?float $totalTraffic): static
    {
        $this->totalTraffic = $totalTraffic;

        return $this;
    }

    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    public function setConnectionType(?string $connectionType): static
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    public function getClientConfig(): ?string
    {
        return $this->clientConfig;
    }

    public function setClientConfig(?string $clientConfig): static
    {
        $this->clientConfig = $clientConfig;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): static
    {
        $this->clientName = $clientName;

        return $this;
    }
}
