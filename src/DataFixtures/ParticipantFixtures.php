<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $participants = [
            ['prenom' => 'Adrien', 'nom' => null, 'isActive' => true],
            ['prenom' => 'Alexandre', 'nom' => null, 'isActive' => false],
            ['prenom' => 'Ophélie', 'nom' => null, 'isActive' => true],
            ['prenom' => 'Alexis', 'nom' => null, 'isActive' => true],
            ['prenom' => 'François', 'nom' => null, 'isActive' => false],
        ];

        foreach ($participants as $data) {
            $participant = new Participant();
            $participant->setPrenom($data['prenom'])
                ->setNom($data['nom'])
                ->setActive($data['isActive']);

            $manager->persist($participant);
        }

        $manager->flush();
    }
}