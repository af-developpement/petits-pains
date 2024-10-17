<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function findEligibleParticipants(?Participant $lastWinner): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($lastWinner) {
            $qb->where('p != :lastWinner')
                ->setParameter('lastWinner', $lastWinner);
        }

        return $qb->getQuery()->getResult();
    }
}
