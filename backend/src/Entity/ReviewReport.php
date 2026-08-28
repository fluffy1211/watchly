<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** A Report whose target is a film review. */
#[ORM\Entity]
class ReviewReport extends Report
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'review_id', onDelete: 'CASCADE')]
    private ?Review $review = null;

    public function getReview(): ?Review { return $this->review; }
    public function setReview(?Review $review): static { $this->review = $review; return $this; }

    public function getTarget(): ?Reportable { return $this->review; }

    public function getType(): string { return 'review'; }
}
