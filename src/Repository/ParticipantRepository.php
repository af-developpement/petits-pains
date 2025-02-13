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

    /**
     * Trouve tous les participants éligibles pour le tirage au sort.
     *
     * @param Participant|null $lastWinner Le dernier gagnant à exclure des résultats
     *
     * @return array<int, Participant> Tableau des entités Participant éligibles
     */
    public function findEligibleParticipants(?Participant $lastWinner): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($lastWinner) {
            $qb->where('p != :lastWinner')
                ->andWhere('p.isActive = :active')
                ->setParameter('lastWinner', $lastWinner)
                ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }
}
