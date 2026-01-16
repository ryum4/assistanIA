<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SyllabusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyllabusRepository::class)]
#[ApiResource]
class Syllabus 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $rawText = null;

    #[ORM\Column(type: "json", nullable: true)]
    private array $extractedCompetences = [];

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'syllabus', targetEntity: CoursePlan::class, cascade: ['persist', 'remove'])]
    private ?CoursePlan $coursePlan = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->extractedCompetences = [];
    }

    public function getId(): ?int 
    { 
        return $this->id;  // ✅ CORRIGÉ
    }

    public function getFilename(): ?string 
    { 
        return $this->filename; 
    }
    
    public function setFilename(?string $filename): self 
    {
        $this->filename = $filename;
        return $this;
    }

    public function getRawText(): ?string 
    { 
        return $this->rawText; 
    }
    
    public function setRawText(?string $rawText): self 
    {
        $this->rawText = $rawText;
        return $this;
    }

    public function getExtractedCompetences(): array
    {
        return $this->extractedCompetences;
    }

    public function setExtractedCompetences(array $extractedCompetences): self
    {
        $this->extractedCompetences = $extractedCompetences;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCoursePlan(): ?CoursePlan
    {
        return $this->coursePlan;
    }

    public function setCoursePlan(?CoursePlan $coursePlan): static
    {
        // unset the owning side of the relation if necessary
        if ($coursePlan === null && $this->coursePlan !== null) {
            $this->coursePlan->setSyllabus(null);
        }

        // set the owning side of the relation if necessary
        if ($coursePlan !== null && $coursePlan->getSyllabus() !== $this) {
            $coursePlan->setSyllabus($this);
        }

        $this->coursePlan = $coursePlan;

        return $this;
    }
}