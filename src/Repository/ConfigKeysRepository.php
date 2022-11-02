<?php

namespace App\Repository;

use App\Entity\ConfigKeys;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConfigKeys|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigKeys|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigKeys[]    findAll()
 * @method ConfigKeys[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigKeysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigKeys::class);
    }

    // /**
    //  * @return ConfigKeysRepository[] Returns an array of ConfigKeysRepository objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConfigKeysRepository
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
