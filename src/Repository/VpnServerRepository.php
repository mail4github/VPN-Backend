<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\VpnServersGetDto;
use App\Entity\VpnServer;
use App\Service\UsefulToolsHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpnServer>
 *
 * @method VpnServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpnServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpnServer[]    findAll()
 * @method VpnServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpnServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpnServer::class);
    }

    public function save(VpnServer $entity, bool $flush = true): void
    {
        $entity->setModified(new \DateTime());

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VpnServer $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByIp(string $ip)
    {
        $res = $this->findOneBy(['ip' => $ip]);
        if (empty($res)) {
            return null;
        }

        return $res;
    }

    public function getServers(
        int $user_id,
        VpnServersGetDto $dto
    )
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'srv.id',
                'srv.is_ready_to_use',
                'srv.service_commission',
                'srv.maximum_active_connections',
                'srv.test_package_until_traffic_volume',
                'srv.test_package_until_traffic_price',
                'srv.test_package_for_period_time',
                'srv.test_package_for_period_price',
                'srv.traffic_vs_period',
                'srv.created',
                'srv.modified',
                'srv.created_by',
                'srv.for_free',
                'srv.price',
                'srv.residential_ip',
                'srv.connection_quality',
                'srv.country',
                'srv.ip',
                'srv.user_name',
                'srv.wallet_address',
                'srv.password',
                'srv.test_packages',
                'srv.paid_packages',
                'srv.protocol'
            )
            ->from('app.public."vpn_server"', 'srv')
            ->where('TRUE')
            ->setFirstResult($dto->offset)
            ->setMaxResults($dto->limit)
            ->orderBy('srv.'.UsefulToolsHelper::sanitizeString($dto->sort_by), $dto->sort_order);
        ;

        // Query the database based on parameters

        switch ($dto->pick_out) {
            case 'subscribed':
                // Must be coded in future
                break;
            case 'favorites':
                $queryBuilder
                ->leftJoin('srv', 'app.public."favorite_server"', 'fvr', 'srv.id = fvr.server_id AND fvr.user_id = :userid')
                ->setParameter('userid', $user_id)
                ->groupBy('srv.id')
                ->andWhere('fvr.server_id IS NOT NULL')
                ;
                break;
            case 'own':
                $queryBuilder->andWhere('srv.user_id = :value')->setParameter('value', $dto->user_id);
                break;
        }

        if (!empty($dto->created_by)) {
            $queryBuilder->andWhere('srv.created_by = :value')->setParameter('value', $dto->created_by);
        }

        if (!empty($dto->country)) {
            $queryBuilder->andWhere('srv.country = :value')->setParameter('value', explode(',', $dto->country));
        }

        if (isset($dto->for_free)) {
            $queryBuilder->andWhere('srv.for_free = :value')->setParameter('value', $dto->for_free);
        } else {
            // If exclude paid servers which are available for limited time then get servers which available to rent for limited traffic only
            if (null != $dto->limited_time_rent_available) {
                $queryBuilder->andWhere('srv.traffic_vs_period = :value')->setParameter('value', 1);
            }

            // If exclude paid servers which are available for limited traffic then get servers which available to rent for limited time only
            if (null != $dto->limited_traffic_rent_available) {
                $queryBuilder->andWhere('srv.traffic_vs_period = :value')->setParameter('value', 0);
            }
        }

        if (!empty($dto->protocol)) {
            $queryBuilder->andWhere('srv.protocol = :value')->setParameter('value', $dto->protocol);
        }

        if (isset($dto->residential_ip)) {
            $queryBuilder->andWhere('srv.residential_ip = :value')->setParameter('value', $dto->residential_ip);
        }

        if (!empty($dto->user_name)) {
            $queryBuilder->andWhere('srv.user_name = :value')->setParameter('value', $dto->user_name);
        }

        if (!empty($dto->ip_address)) {
            $queryBuilder->andWhere('srv.ip = :value')->setParameter('value', $dto->ip_address);
        }

        //echo $queryBuilder->getSQL(); exit; // SHOW SQL

        return $queryBuilder->execute()->fetchAll();
 
    }
}
