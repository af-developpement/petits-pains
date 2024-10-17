<?php

namespace App\Command;

use App\Entity\Tirage;
use App\Repository\ParticipantRepository;
use App\Repository\TirageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:tirage',
    description: 'Tire au sort un participant de la semaine',
)]
class TirageAuSortCommand extends Command
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private TirageRepository $tirageRepository,
        private HttpClientInterface $httpClient,
        private string $slackWebhookUrl
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dernierTirage = $this->tirageRepository->findLastTirage();

        $participantsEligibles = $this->participantRepository->findEligibleParticipants(
            $dernierTirage?->getParticipant()
        );

        if (empty($participantsEligibles)) {
            return Command::FAILURE;
        }

        // Tirer au sort un participant
        $gagnant = $participantsEligibles[array_rand($participantsEligibles)];

        // Créer le nouveau tirage
        $tirage = new Tirage();
        $tirage->setParticipant($gagnant);
        $tirage->setDateTirage(new \DateTime());

        // Sauvegarder le tirage
        $this->tirageRepository->save($tirage, true);

        // Envoyer la notification Slack
        $this->sendSlackNotification($gagnant->getPrenom(), $gagnant->getNom());

        return Command::SUCCESS;
    }

    private function sendSlackNotification(string $prenom, ?string $nom): void
    {
        $nomComplet = trim($prenom . ' ' . ($nom ?? ''));
        $message = [
            'text' => sprintf(
                "🎉 *Tirage au sort de la semaine* 🎉\nFélicitations à *%s* qui a été tiré(e) au sort cette semaine !",
                $nomComplet
            )
        ];

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => $message
        ]);
    }
}