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
            ['prenom' => 'Adrien', 'nom' => 'Dupont', 'isActive' => true],
            ['prenom' => 'Alexandre', 'nom' => 'Martin', 'isActive' => false],
            ['prenom' => 'Ophélie', 'nom' => 'Vantours', 'isActive' => true],
            ['prenom' => 'Alexis', 'nom' => 'Bernard', 'isActive' => true],
            ['prenom' => 'François', 'nom' => 'Lemoine', 'isActive' => false],
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