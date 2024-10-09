<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VpnServerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'vpn_server')]
#[ORM\Entity(repositoryClass: VpnServerRepository::class)]
class VpnServer
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'vpn_server_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\Column(name: 'country', type: 'text', nullable: false)]
    private string $country;

    #[ORM\Column(name: 'ip', type: 'text', nullable: true)]
    private ?string $ip;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTimeInterface $created;

    #[ORM\Column(name: 'modified', type: 'datetime', nullable: false)]
    private \DateTimeInterface $modified;

    #[ORM\Column(name: 'created_by', type: 'integer', nullable: true, options: ['comment' => 'User id of a person who added this server'])]
    private ?int $createdBy;

    #[ORM\Column(name: 'for_free', type: 'boolean', nullable: true, options: ['comment' => 'Is it possible to connect to this server for free'])]
    private ?bool $forFree;

    #[ORM\Column(name: 'price', type: 'float', precision: 10, scale: 0, nullable: true)]
    private ?float $price;

    #[ORM\Column(name: 'protocol', type: 'text', nullable: true, options: ['default' => 'WireGuard', 'comment' => 'Possible values: WireGuard, OpenVPN (UDP), OpenVPN (TCP)'])]
    private ?string $protocol = 'WireGuard';

    #[ORM\Column(name: 'user_name', type: 'text', nullable: true)]
    private ?string $userName;

    #[ORM\Column(name: 'residential_ip', type: 'boolean', nullable: true, options: ['comment' => 'Is this server located at a residential area'])]
    private ?bool $residentialIp;

    #[ORM\Column(name: 'connection_quality', type: 'integer', nullable: true, options: ['comment' => '0 - is best quality, 1 - fair quality, 2 - poor quality'])]
    private ?int $connectionQuality;

    #[ORM\Column(name: 'service_commission', type: 'float', precision: 10, scale: 0, nullable: true, options: ['comment' => 'A commission for this service in percents'])]
    private ?float $serviceCommission;

    #[ORM\Column(name: 'maximum_active_connections', type: 'integer', nullable: true, options: ['comment' => 'Number of maximum active connections. If zero then no limits'])]
    private ?int $maximumActiveConnections;

    #[ORM\Column(name: 'test_package_until_traffic_volume', type: 'float', precision: 10, scale: 0, nullable: true, options: ['comment' => 'The volume of traffic for the test package'])]
    private ?float $testPackageUntilTrafficVolume;

    #[ORM\Column(name: 'test_package_until_traffic_price', type: 'float', precision: 10, scale: 0, nullable: true, options: ['comment' => 'The price of test package with limited traffic'])]
    private ?float $testPackageUntilTrafficPrice;

    #[ORM\Column(name: 'test_package_for_period_time', type: 'float', precision: 10, scale: 0, nullable: true, options: ['comment' => 'The duration of the test package which is active during a period of time'])]
    private ?float $testPackageForPeriodTime;

    #[ORM\Column(name: 'test_package_for_period_price', type: 'float', precision: 10, scale: 0, nullable: true, options: ['comment' => 'The price of limited time test package'])]
    private ?float $testPackageForPeriodPrice;

    #[ORM\Column(name: 'traffic_vs_period', type: 'boolean', nullable: true, options: ['default' => '1', 'comment' => 'If true then the traffic is active. If false then period of time'])]
    private ?bool $trafficVsPeriod = true;

    #[ORM\Column(name: 'password', type: 'text', nullable: false)]
    private string $password;

    #[ORM\Column(name: 'test_packages', type: 'text', nullable: true, options: ['default' => '[]', 'comment' => 'a JSON text with array of test packages'])]
    private ?string $testPackages = '[]';

    #[ORM\Column(name: 'paid_packages', type: 'text', nullable: true, options: ['default' => '[]', 'comment' => 'a JSON text with array of paid packages'])]
    private ?string $paidPackages = '[]';

    #[ORM\Column(
        name: 'wallet_address',
        type: 'text',
        nullable: false,
        options: [
            'comment' => 'A wallet address for receiving rewards from the sale of packages for access to the VPN server',
        ]
    )]
    private string $walletAddress;

    #[ORM\Column(
        name: 'is_ready_to_use',
        type: Types::BOOLEAN,
        nullable: false,
        options: [
            'default' => 'false',
            'comment' => 'Flag indicates that server ready to use or not',
        ]
    )]
    private bool $isReadyToUse = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
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

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): static
    {
        $this->modified = $modified;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isForFree(): ?bool
    {
        return $this->forFree;
    }

    public function setForFree(?bool $forFree): static
    {
        $this->forFree = $forFree;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

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

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function isResidentialIp(): ?bool
    {
        return $this->residentialIp;
    }

    public function setResidentialIp(?bool $residentialIp): static
    {
        $this->residentialIp = $residentialIp;

        return $this;
    }

    public function getConnectionQuality(): ?int
    {
        return $this->connectionQuality;
    }

    public function setConnectionQuality(?int $connectionQuality): static
    {
        $this->connectionQuality = $connectionQuality;

        return $this;
    }

    public function getServiceCommission(): ?float
    {
        return $this->serviceCommission;
    }

    public function setServiceCommission(?float $serviceCommission): static
    {
        $this->serviceCommission = $serviceCommission;

        return $this;
    }

    public function getMaximumActiveConnections(): ?int
    {
        return $this->maximumActiveConnections;
    }

    public function setMaximumActiveConnections(?int $maximumActiveConnections): static
    {
        $this->maximumActiveConnections = $maximumActiveConnections;

        return $this;
    }

    public function getTestPackageUntilTrafficVolume(): ?float
    {
        return $this->testPackageUntilTrafficVolume;
    }

    public function setTestPackageUntilTrafficVolume(?float $testPackageUntilTrafficVolume): static
    {
        $this->testPackageUntilTrafficVolume = $testPackageUntilTrafficVolume;

        return $this;
    }

    public function getTestPackageUntilTrafficPrice(): ?float
    {
        return $this->testPackageUntilTrafficPrice;
    }

    public function setTestPackageUntilTrafficPrice(?float $testPackageUntilTrafficPrice): static
    {
        $this->testPackageUntilTrafficPrice = $testPackageUntilTrafficPrice;

        return $this;
    }

    public function getTestPackageForPeriodTime(): ?float
    {
        return $this->testPackageForPeriodTime;
    }

    public function setTestPackageForPeriodTime(?float $testPackageForPeriodTime): static
    {
        $this->testPackageForPeriodTime = $testPackageForPeriodTime;

        return $this;
    }

    public function getTestPackageForPeriodPrice(): ?float
    {
        return $this->testPackageForPeriodPrice;
    }

    public function setTestPackageForPeriodPrice(?float $testPackageForPeriodPrice): static
    {
        $this->testPackageForPeriodPrice = $testPackageForPeriodPrice;

        return $this;
    }

    public function isTrafficVsPeriod(): ?bool
    {
        return $this->trafficVsPeriod;
    }

    public function setTrafficVsPeriod(?bool $trafficVsPeriod): static
    {
        $this->trafficVsPeriod = $trafficVsPeriod;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getTestPackages(): ?string
    {
        return $this->testPackages;
    }

    public function setTestPackages(?string $testPackages): static
    {
        $this->testPackages = $testPackages;

        return $this;
    }

    public function getPaidPackages(): ?string
    {
        return $this->paidPackages;
    }

    public function setPaidPackages(?string $paidPackages): static
    {
        $this->paidPackages = $paidPackages;

        return $this;
    }

    public function getWalletAddress(): ?string
    {
        return $this->walletAddress;
    }

    public function setWalletAddress(string $walletAddress): static
    {
        $this->walletAddress = $walletAddress;

        return $this;
    }

    public function isReadyToUse(): bool
    {
        return $this->isReadyToUse;
    }

    public function setIsReadyToUse(bool $isReadyToUse): static
    {
        $this->isReadyToUse = $isReadyToUse;

        return $this;
    }
}
