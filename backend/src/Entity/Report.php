<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A user's flag on a Reportable, with an optional reason.
 *
 * Single-table inheritance: one `report` table, a `discr` column, and one
 * subclass per target type (CommentReport, ReviewReport). Everything shared —
 * reporter, reason, timestamp — lives here; each subclass adds one FK to its
 * target and answers getTarget() / getType().
 */
#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(name: 'report')]
#[ORM\UniqueConstraint(name: 'uq_report_comment_reporter', columns: ['comment_id', 'reporter_id'])]
#[ORM\UniqueConstraint(name: 'uq_report_review_reporter', columns: ['review_id', 'reporter_id'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([
    'comment' => CommentReport::class,
    'review'  => ReviewReport::class,
])]
abstract class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    /** The reported content, or null if it has since been deleted. */
    abstract public function getTarget(): ?Reportable;

    /** Discriminator value: 'comment' | 'review'. */
    abstract public function getType(): string;

    public function getId(): ?int { return $this->id; }

    public function getReporter(): ?User { return $this->reporter; }
    public function setReporter(?User $reporter): static { $this->reporter = $reporter; return $this; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): static { $this->reason = $reason; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
