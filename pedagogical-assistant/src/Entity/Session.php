<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::JSON)]
    private array $objectifs = [];

    #[ORM\Column(type: Types::JSON)]
    private array $contenus = [];

    #[ORM\Column(type: Types::JSON)]
    private array $activites = [];

    #[ORM\Column]
    private bool $done = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesReelles = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CoursePlan $coursePlan = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Exercise::class, orphanRemoval: true)]
    private Collection $exercises;

    public function __construct()
    {
        $this->exercises = new ArrayCollection();
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

    public function getObjectifs(): array
    {
        return $this->objectifs;
    }

    public function setObjectifs(array $objectifs): static
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getContenus(): array
    {
        return $this->contenus;
    }

    public function setContenus(array $contenus): static
    {
        $this->contenus = $contenus;

        return $this;
    }

    public function getActivites(): array
    {
        return $this->activites;
    }

    public function setActivites(array $activites): static
    {
        $this->activites = $activites;

        return $this;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): static
    {
        $this->done = $done;

        return $this;
    }

    public function getNotesReelles(): ?string
    {
        return $this->notesReelles;
    }

    public function setNotesReelles(?string $notesReelles): static
    {
        $this->notesReelles = $notesReelles;

        return $this;
    }

    public function getCoursePlan(): ?CoursePlan
    {
        return $this->coursePlan;
    }

    public function setCoursePlan(?CoursePlan $coursePlan): static
    {
        $this->coursePlan = $coursePlan;

        return $this;
    }

    /**
     * @return Collection<int, Exercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }

    public function addExercise(Exercise $exercise): static
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises->add($exercise);
            $exercise->setSession($this);
        }

        return $this;
    }

    public function removeExercise(Exercise $exercise): static
    {
        if ($this->exercises->removeElement($exercise)) {
            if ($exercise->getSession() === $this) {
                $exercise->setSession(null);
            }
        }

        return $this;
    }
}