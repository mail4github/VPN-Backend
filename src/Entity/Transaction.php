<?php

declare(strict_types=1);

namespace App\Entity;

// use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'transaction')]
#[ORM\Entity]
class Transaction
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['default' => '1'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'transaction_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id = 1;

    #[ORM\Column(
        name: 'user_id',
        type: 'integer',
        nullable: false,
        options: ['comment' => 'ID of the owner of this transaction in the User table']
    )]
    private int $userId;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTimeInterface $created;

    #[ORM\Column(name: 'modified', type: 'datetime', nullable: false)]
    private \DateTimeInterface $modified;

    #[ORM\Column(
        name: 'tr_type',
        type: 'string',
        length: 16,
        nullable: false,
        options: [
            'default' => 'ADD',
            'comment' => 'The transaction type. Values: ADD - Accrual, SUB - Charging-off',
        ]
    )]
    private string $trType = 'ADD';
    /*
    #[ORM\Column(
        name: 'server_id',
        type: 'integer',
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'ID of VPN server in the vpn_server table'
        ]
    )]
    private int $serverId = 0;
    */
    #[ORM\Column(
        name: 'items_spent',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'Number of access entities which user spent to get access to the server',
        ]
    )]
    private ?float $itemsSpent = 0;

    #[ORM\Column(
        name: 'items_total',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: false,
        options: [
            'default' => '0',
            'comment' => 'Total number of access entities which have been purchased by user to get access to the server',
        ]
    )]
    private float $itemsTotal = 0;

    #[ORM\Column(
        name: 'item_name',
        type: 'string',
        length: 32,
        nullable: true,
        options: [
            'default' => 'Day',
            'comment' => 'Name of access entities which have been purchased by user to get access to the server. Example: Day, Gb',
        ]
    )]
    private ?string $itemName = 'Day';

    #[ORM\Column(
        name: 'amount',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: false,
        options: [
            'default' => '0',
            'comment' => 'The value of transaction',
        ]
    )]
    private float $amount = 0;

    #[ORM\Column(
        name: 'currency',
        type: 'string',
        length: 5,
        nullable: false,
        options: ['default' => 'NDS']
    )]
    private string $currency = 'NDS';

    #[ORM\Column(
        name: 'status',
        type: 'string',
        length: 2,
        nullable: false,
        options: [
            'default' => 'A',
            'comment' => 'A - approved, P - pending, D - declined',
        ]
    )]
    private string $status = 'A';

    #[ORM\Column(
        name: 'date_will_active',
        type: 'datetime',
        nullable: true,
        options: ['comment' => 'A date when this transaction will be activated automatically']
    )]
    private ?\DateTimeInterface $dateWillActive;

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
    private string $description = '';

    #[ORM\Column(
        name: 'balance',
        type: 'float',
        precision: 10,
        scale: 0,
        nullable: true,
        options: [
            'comment' => 'The balance for that currency calculated at the moment of transaction',
        ]
    )]
    private ?float $balance;

    #[ORM\Column(
        name: 'txid',
        type: 'string',
        length: 1024,
        nullable: true,
        options: [
            'default' => '',
            'comment' => 'Transaction hash from blockchain',
        ]
    )]
    private string $txid = '';

    #[ORM\Column(
        name: 'crc',
        type: 'string',
        length: 255,
        nullable: true,
        options: [
            'default' => '',
            'comment' => 'The control sum of this transaction',
        ]
    )]
    private string $crc = '';

    #[ORM\Column(
        name: 'connection_id',
        type: 'integer',
        nullable: true,
        options: [
            'default' => '0',
            'comment' => 'ID of VPN connection in the vpn_connection table',
        ]
    )]
    private int $connectionId = 0;

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

    public function getTrType(): ?string
    {
        return $this->trType;
    }

    public function setTrType(string $trType): static
    {
        $this->trType = $trType;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDateWillActive(): ?\DateTimeInterface
    {
        return $this->dateWillActive;
    }

    public function setDateWillActive(?\DateTimeInterface $dateWillActive): static
    {
        $this->dateWillActive = $dateWillActive;

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

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTxid(): ?string
    {
        return $this->txid;
    }

    public function setTxid(?string $txid): static
    {
        $this->txid = $txid;

        return $this;
    }

    public function getCrc(): ?string
    {
        return $this->crc;
    }

    public function setCrc(?string $crc): static
    {
        $this->crc = $crc;

        return $this;
    }

    public function getItemsSpent(): ?float
    {
        return $this->itemsSpent;
    }

    public function setItemsSpent(?float $itemsSpent): static
    {
        $this->itemsSpent = $itemsSpent;

        return $this;
    }

    public function getItemsTotal(): ?float
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal(?float $itemsTotal): static
    {
        $this->itemsTotal = $itemsTotal;

        return $this;
    }

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function setItemName(?string $itemName): static
    {
        $this->itemName = $itemName;

        return $this;
    }

    /*
    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(int $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }
    */
    public function getConnectionId(): ?int
    {
        return $this->connectionId;
    }

    public function setConnectionId(int $connectionId): static
    {
        $this->connectionId = $connectionId;

        return $this;
    }
}
