<?php

namespace App\Entity\Matchfunding;

use App\Entity\Project\Project;
use App\Repository\Matchfunding\MatchCallSubmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchCallSubmissionRepository::class)]
class MatchCallSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'matchCallSubmissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchCall $matchCall = null;

    #[ORM\ManyToOne(inversedBy: 'matchCallSubmissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(enumType: MatchCallSubmissionStatus::class)]
    private ?MatchCallSubmissionStatus $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatchCall(): ?MatchCall
    {
        return $this->matchCall;
    }

    public function setMatchCall(?MatchCall $matchCall): static
    {
        $this->matchCall = $matchCall;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getStatus(): ?MatchCallSubmissionStatus
    {
        return $this->status;
    }

    public function setStatus(MatchCallSubmissionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}