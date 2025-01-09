<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function findVotesByUserInSondage(int $userId, int $sondageId): array
    {
        // recuperer les choix qui ont été voté par l'utilisateur dans le sondage
        // choix.sondage_id = vote.id_choix_id et vote.user_id = $userId

        return $this->createQueryBuilder('v')
            ->select('v')
            ->innerJoin('v.idChoix', 'c')
            ->where('v.user = :userId')
            ->andWhere('c.sondage = :sondageId')
            ->setParameter('userId', $userId)
            ->setParameter('sondageId', $sondageId)
            ->getQuery()
            ->getResult();

    }





    //    /**
    //     * @return Vote[] Returns an array of Vote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vote
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
