<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EmailVerificationCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailVerificationCode>
 *
 * @method EmailVerificationCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailVerificationCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailVerificationCode[]    findAll()
 * @method EmailVerificationCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailVerificationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailVerificationCode::class);
    }

    public function save(EmailVerificationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailVerificationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByUserAndValue(User $user, string $value): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.code = :code')
            ->andWhere('e.owner = :user')
            ->setParameter('code', $value)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByCode(string $value): ?EmailVerificationCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.code = :code')
            ->setParameter('code', $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
