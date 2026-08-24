<?php

namespace App\Entity;

use App\Repository\ReviewReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewReportRepository::class)]
#[ORM\Table(name: 'review_report')]
#[ORM\UniqueConstraint(name: 'uq_review_report', columns: ['review_id', 'reporter_id'])]
class ReviewReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'review_id', nullable: false, onDelete: 'CASCADE')]
    private ?Review $review = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'reporter_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $reporter = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getReview(): ?Review { return $this->review; }
    public function setReview(?Review $review): static { $this->review = $review; return $this; }

    public function getReporter(): ?User { return $this->reporter; }
    public function setReporter(?User $reporter): static { $this->reporter = $reporter; return $this; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): static { $this->reason = $reason; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
