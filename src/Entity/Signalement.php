<?php

namespace App\Entity;

use App\Repository\SignalementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
class Signalement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $raison = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $userSignaler = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $userSignaleur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;

        return $this;
    }

    public function getUserSignaler(): ?Utilisateur
    {
        return $this->userSignaler;
    }

    public function setUserSignaler(?Utilisateur $userSignaler): static
    {
        $this->userSignaler = $userSignaler;

        return $this;
    }

    public function getUserSignaleur(): ?Utilisateur
    {
        return $this->userSignaleur;
    }

    public function setUserSignaleur(?Utilisateur $userSignaleur): static
    {
        $this->userSignaleur = $userSignaleur;

        return $this;
    }
}
