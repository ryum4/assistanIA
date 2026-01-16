<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CoursePlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: CoursePlanRepository::class)]
class CoursePlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $planGeneral = null;

    #[ORM\Column(type: Types::JSON)]
    private array $evaluation = [];

    #[ORM\OneToOne(inversedBy: 'coursePlan', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Syllabus $syllabus = null;

    #[ORM\OneToMany(mappedBy: 'coursePlan', targetEntity: Session::class, orphanRemoval: true, cascade: ['persist'])]
    #[ApiProperty(fetchEager: true)]
    private Collection $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlanGeneral(): ?string
    {
        return $this->planGeneral;
    }

    public function setPlanGeneral(?string $planGeneral): static
    {
        $this->planGeneral = $planGeneral;

        return $this;
    }

    public function getEvaluation(): array
    {
        return $this->evaluation;
    }

    public function setEvaluation(array $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function getSyllabus(): ?Syllabus
    {
        return $this->syllabus;
    }

    public function setSyllabus(Syllabus $syllabus): static
    {
        $this->syllabus = $syllabus;

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCoursePlan($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getCoursePlan() === $this) {
                $session->setCoursePlan(null);
            }
        }

        return $this;
    }
}