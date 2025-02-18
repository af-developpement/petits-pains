<?php

namespace App\Command;

use App\Entity\Tirage;
use App\Repository\ParticipantRepository;
use App\Repository\TirageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:tirage',
    description: 'Tire au sort un participant de la semaine',
)]
class TirageAuSortCommand extends Command
{
    private string $activeWebhookUrl;

    public function __construct(
        private ParticipantRepository $participantRepository,
        private TirageRepository $tirageRepository,
        private HttpClientInterface $httpClient,
        string $slackWebhookUrl,
        ?string $slackWebhookTestUrl = null,
    ) {
        parent::__construct();

        $this->activeWebhookUrl = !empty($slackWebhookTestUrl)
            ? $slackWebhookTestUrl
            : $slackWebhookUrl;
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

        $gagnant = $participantsEligibles[array_rand($participantsEligibles)];

        $tirage = new Tirage();
        $tirage->setParticipant($gagnant);
        $tirage->setDateTirage(new \DateTime());

        $this->tirageRepository->save($tirage, true);

        $this->sendSlackNotification($gagnant->getPrenom(), $gagnant->getNom());

        return Command::SUCCESS;
    }

    private function sendSlackNotification(string $prenom, ?string $nom): void
    {
        $nomComplet = trim($prenom.' '.($nom ?? ''));
        $message = [
            'text' => sprintf(
                "🎉 *Tirage au sort de la semaine* 🎉\nFélicitations à *%s* qui a été tiré(e) au sort cette semaine !",
                $nomComplet
            ),
        ];

        $this->httpClient->request('POST', $this->activeWebhookUrl, [
            'json' => $message,
        ]);
    }
}
