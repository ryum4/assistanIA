<?php

namespace App\Repository;

use App\Entity\Exercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercise>
 */
class ExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercise::class);
    }

    public function findBySession(int $sessionId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.session = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
