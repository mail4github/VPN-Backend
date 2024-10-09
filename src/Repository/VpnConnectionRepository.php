<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\VpnConnectionAddDto;
use App\Entity\User;
use App\Entity\VpnConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpnConnection>
 *
 * @method VpnConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpnConnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpnConnection[]    findAll()
 * @method VpnConnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpnConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpnConnection::class);
    }

    public function findByClientName(string $clientName): ?VpnConnection
    {
        $qb = $this->createQueryBuilder('conn');
        $qb
            ->where('conn.clientName = :clientName')
            ->setParameter('clientName', $clientName)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function add(VpnConnectionAddDto $dto): VpnConnection
    {
        $vpnConnection = new VpnConnection();
        $vpnConnection->setUserId($dto->user_id);
        $vpnConnection->setIp($dto->ip);
        $vpnConnection->setCountry($dto->country);
        $vpnConnection->setCreated(new \DateTime());
        $vpnConnection->setModified(new \DateTime());
        $vpnConnection->setServerId($dto->server_id);
        $vpnConnection->setProtocol($dto->protocol);

        if (null != $dto->duration) {
            $vpnConnection->setDuration($dto->duration);
        }

        if (null != $dto->total_traffic) {
            $vpnConnection->setTotalTraffic($dto->total_traffic);
        }

        if (null != $dto->description) {
            $vpnConnection->setDescription($dto->description);
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->find($dto->user_id);

        $clientNameBase = $user?->getLogin() ? $user->getLogin() : $dto->user_id;
        $vpnConnection->setClientName($clientNameBase.'-'.$dto->server_id);

        $this->getEntityManager()->persist($vpnConnection);
        $this->getEntityManager()->flush();

        return $vpnConnection;
    }
}
