<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageChoix = null;
    #[Vich\UploadableField(mapping: 'choix_image', fileNameProperty: 'imageChoix')]
    private ?File $imageChoixFile = null;
    #[ORM\ManyToOne(inversedBy: 'choix')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sondage $sondage = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'idChoix')]
    private Collection $votes;



    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImageChoix(): ?string
    {
        return $this->imageChoix;
    }

    public function setImageChoix(?string $imageChoix): static
    {
        $this->imageChoix = $imageChoix;

        return $this;
    }
    public function setImageChoixFile(?File $file = null): void
    {
        $this->imageChoixFile = $file;

    }

    public function getImageChoixFile(): ?File
    {
        return $this->imageChoixFile;
    }

    public function getSondage(): ?Sondage
    {
        return $this->sondage;
    }

    public function setSondage(?Sondage $sondage): static
    {
        $this->sondage = $sondage;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setIdChoix($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getIdChoix() === $this) {
                $vote->setIdChoix(null);
            }
        }

        return $this;
    }


}
