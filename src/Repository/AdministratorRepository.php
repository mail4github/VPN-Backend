<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Administrator;
use App\Service\UsefulToolsHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Administrator>
 *
 * @method Administrator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Administrator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Administrator[]    findAll()
 * @method Administrator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministratorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administrator::class);
    }

    public function save(Administrator $entity, bool $flush = true): void
    {
        $entity->setModified(new \DateTime());

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Administrator $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getListOfAdministrators(
        int $adminId = NULL,
        string $LoginOrDescriptionOrRoleNameEquals = '',
        int $limit = 24,
        string $sortBy = 'created', 
        string $sortOrder = 'asc',
        int $offset = 0,
    )
    {
        $sortBy = mb_strtolower($sortBy);
        
        $sortOrder = mb_strtolower($sortOrder);
        
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                'admin.id',
                'admin.created',
                'admin.modified',
                'admin.login',
                'admin.last_login',
                'admin.login',
                'admin.description',
                'admin.pgp_public_key',
                'admin.superadmin',
                'admin.blocked',
                'CASE WHEN MIN(admrole.role_id) IS NOT NULL
                    THEN \'[\' ||
                        STRING_AGG(\'{"id":\' || cast(admrole.role_id as varchar)
                            || \',"name":"\' || rle.name
                            || \'","permissions":\' || rle.permissions
                        || \'}\'
                    , \',\') || \']\'
                    ELSE \'[]\'
                    END AS roles'
            )
            ->from('app.public."administrator"', 'admin')
            ->leftJoin('admin', 'app.public."adminrole"', 'admrole', 'admin.id = admrole.admin_id')
            ->leftJoin('admrole', 'app.public."role"', 'rle', 'rle.id = admrole.role_id')
            ->groupBy('admin.id')
            ->where('TRUE')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('admin.'.$sortBy, $sortOrder)
        ;
        
        if (NULL !== $adminId) {
            $queryBuilder
                ->andWhere('admin.id = :admin_id')
                ->setParameter('admin_id', $adminId)
            ;
        }

        if (!empty($LoginOrDescriptionOrRoleNameEquals)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orx()
                    ->add($queryBuilder->expr()->like('admin.login', ':search'))
                    ->add($queryBuilder->expr()->like('admin.description', ':search'))
                    ->add($queryBuilder->expr()->like('rle.name', ':search'))
            )->setParameter('search', '%'.$LoginOrDescriptionOrRoleNameEquals.'%')
            ;
        }
                
        // echo $queryBuilder->getSQL(); exit; // SHOW SQL

        return $queryBuilder->execute()->fetchAll();

    }
}