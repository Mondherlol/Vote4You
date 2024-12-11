<?php

namespace App\Repository;

use App\Entity\Sondage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sondage>
 */
class SondageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sondage::class);
    }

    //    /**
    //     * @return Sondage[] Returns an array of Sondage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sondage
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByTitleLike(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.titre LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function findSondagesWithVoteCount(): array
    {
        $query = $this->createQueryBuilder('s')
            ->select('s AS sondage', 'COUNT(v.id) AS voteCount') // Sélectionner les sondages, les votes
            ->leftJoin('s.choix', 'c') // Relation entre Sondage et Choix
            ->leftJoin('c.votes', 'v') // Relation entre Choix et Vote
            ->groupBy('s.id') // Grouper par sondage
            ->getQuery();

        // Reformatage des résultats pour un accès plus simple
        return array_map(function ($result) {
            return [
                'sondage' => $result['sondage'],
                'voteCount' => (int) $result['voteCount'],
            ];
        }, $query->getResult());
    }





}
