<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(
    name: 'favorite_server',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'fav_srv_uniq_server_user', columns: ['user_id', 'server_id']),
    ]
)]
#[ORM\Entity]
class FavoriteServer
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['default' => '1'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'favorite_server_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = 1;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    private int $userId;

    #[ORM\Column(name: 'server_id', type: 'integer', nullable: false)]
    private int $serverId;

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

    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(int $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }
}
