<?php

namespace App\Tests;

use App\Command\TirageAuSortCommand;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Repository\TirageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TirageAuSortCommandTest extends KernelTestCase
{
    public function testTirageClassique(): void
    {
        $participant = new Participant();
        $participant->setNom('Dupont');
        $participant->setPrenom('Jean');
        $participant->setActive(true);

        $this->assertEquals('Dupont', $participant->getNom());
        $this->assertEquals('Jean', $participant->getPrenom());
        $this->assertTrue($participant->isActive());
    }

    public function testTirageAvecParticipantInactif(): void
    {
        $participant = new Participant();
        $participant->setNom('Durand');
        $participant->setPrenom('Marie');
        $participant->setActive(false);

        $this->assertEquals('Durand', $participant->getNom());
        $this->assertEquals('Marie', $participant->getPrenom());
        $this->assertFalse($participant->isActive());
    }

    public function testExecuteWithEligibleParticipants(): void
    {
        // Mock du Participant
        $participant = new Participant();
        $participant->setPrenom('Jean')->setNom('Dupont')->setActive(true);

        // Mock du TirageRepository
        $tirageRepository = $this->createMock(TirageRepository::class);
        $tirageRepository
            ->method('findLastTirage')
            ->willReturn(null); // Pas de dernier tirage
        $tirageRepository
            ->expects($this->once())
            ->method('save'); // S'assurer que save est appelé

        // Mock du ParticipantRepository
        $participantRepository = $this->createMock(ParticipantRepository::class);
        $participantRepository
            ->method('findEligibleParticipants')
            ->willReturn([$participant]); // Retourne une liste avec un seul participant

        // Mock du HttpClient
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('request') // S'assurer que la notification Slack est envoyée
            ->with(
                'POST',
                'SLACK_WEBHOOK_URL', // Remplacez par l'URL réelle si nécessaire
                $this->callback(function ($options) {
                    $this->assertArrayHasKey('json', $options);
                    $this->assertStringContainsString('Jean Dupont', $options['json']['text']);
                    return true;
                })
            );

        // Création de la commande
        $command = new TirageAuSortCommand(
            $participantRepository,
            $tirageRepository,
            $httpClient,
            'SLACK_WEBHOOK_URL'
        );

        // Tester la commande
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);

        // Vérifications
        $this->assertSame(0, $exitCode); // Vérifier le succès
    }

    public function testExecuteWithNoEligibleParticipants(): void
    {
        // Mock du TirageRepository
        $tirageRepository = $this->createMock(TirageRepository::class);
        $tirageRepository
            ->method('findLastTirage')
            ->willReturn(null);

        // Mock du ParticipantRepository
        $participantRepository = $this->createMock(ParticipantRepository::class);
        $participantRepository
            ->method('findEligibleParticipants')
            ->willReturn([]); // Aucun participant éligible

        // Mock du HttpClient
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects($this->never())
            ->method('request'); // Pas d'appel à Slack

        // Création de la commande
        $command = new TirageAuSortCommand(
            $participantRepository,
            $tirageRepository,
            $httpClient,
            'SLACK_WEBHOOK_URL'
        );

        // Tester la commande
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);

        // Vérifications
        $this->assertSame(1, $exitCode); // Vérifier l'échec
    }
}