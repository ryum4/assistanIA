<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExerciseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
class Exercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Session $session = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $consigne = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $correction = null;

    #[ORM\Column(length: 50)]
    private ?string $difficulte = 'moyen'; // facile, moyen, difficile

    #[ORM\Column(type: Types::JSON)]
    private array $competencesCiblees = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
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

    public function getConsigne(): ?string
    {
        return $this->consigne;
    }

    public function setConsigne(string $consigne): static
    {
        $this->consigne = $consigne;

        return $this;
    }

    public function getCorrection(): ?array
    {
        return $this->correction;
    }

    public function setCorrection(?array $correction): static
    {
        $this->correction = $correction;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): static
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getCompetencesCiblees(): array
    {
        return $this->competencesCiblees;
    }

    public function setCompetencesCiblees(array $competencesCiblees): static
    {
        $this->competencesCiblees = $competencesCiblees;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
