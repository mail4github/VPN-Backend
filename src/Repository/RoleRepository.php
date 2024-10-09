<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\RolesGetDto;
use App\Entity\Role;
use App\Service\UsefulToolsHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function save(Role $entity, bool $flush = true): void
    {
        $entity->setModified(new \DateTime());

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getListOfRoles(
        RolesGetDto $dto
    )
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'rl.id',
                'rl.created',
                'rl.modified',
                'rl.name',
                'rl.permissions'
            )
            ->from('app.public."role"', 'rl')
            ->where('TRUE')
            ->setFirstResult($dto->offset)
            ->setMaxResults($dto->limit)
            ->orderBy($dto->sort_by, $dto->sort_order)
        ;

        if (!empty($dto->search)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->like('rl.name', ':search')
            )
            ->setParameter('search', '%'.$dto->search.'%')
            ;
        }
        
        $roles = $queryBuilder->execute()->fetchAll();
        for ($i = 0; $i < \count($roles); ++$i) {
            $roles[$i]['permissions'] = json_decode($roles[$i]['permissions']);
        }

        return $roles;
    }

    public function searchOtherRoleWithSameName(
        string $name,
        int $id
    )
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('rl.id')
            ->from('app.public."role"', 'rl')
            ->where('rl.name = :name')->setParameter('name', $name)
            ->andWhere('rl.id <> :rl_id')->setParameter('rl_id', $id)
            ->setMaxResults(1)
        ;
        //echo $queryBuilder->getSQL(); exit; // SHOW SQL
        return $queryBuilder->execute()->fetchAll();
    }
}