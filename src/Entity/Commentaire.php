<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $idOwner = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sondage $idSondage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getIdOwner(): ?Utilisateur
    {
        return $this->idOwner;
    }

    public function setIdOwner(?Utilisateur $idOwner): static
    {
        $this->idOwner = $idOwner;

        return $this;
    }

    public function getIdSondage(): ?Sondage
    {
        return $this->idSondage;
    }

    public function setIdSondage(?Sondage $idSondage): static
    {
        $this->idSondage = $idSondage;

        return $this;
    }
}
