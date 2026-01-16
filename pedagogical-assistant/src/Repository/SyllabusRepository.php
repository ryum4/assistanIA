<?php

namespace App\Repository;

use App\Entity\Syllabus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Syllabus>
 */
class SyllabusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Syllabus::class);
    }

    /**
     * Find a syllabus by filename
     */
    public function findByFilename(string $filename): ?Syllabus
    {
        return $this->findOneBy(['filename' => $filename]);
    }

    /**
     * Find all syllabuses ordered by creation date
     */
    public function findAllOrderedByDate(): array
    {
        return $this->findBy([], ['createdAt' => 'DESC']);
    }
}
