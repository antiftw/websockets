<?php

namespace App\Repository;

use App\Entity\WebsocketConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsocketConnection>
 *
 * @method WebsocketConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebsocketConnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebsocketConnection[]    findAll()
 * @method WebsocketConnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebsocketConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsocketConnection::class);
    }

    //    /**
    //     * @return WebsocketConnection[] Returns an array of WebsocketConnection objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WebsocketConnection
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
