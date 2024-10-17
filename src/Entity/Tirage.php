<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Tirage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateTirage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;
        return $this;
    }

    public function getDateTirage(): ?\DateTime
    {
        return $this->dateTirage;
    }

    public function setDateTirage(\DateTime $dateTirage): self
    {
        $this->dateTirage = $dateTirage;
        return $this;
    }
}