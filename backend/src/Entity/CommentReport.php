<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** A Report whose target is a list comment. */
#[ORM\Entity]
class CommentReport extends Report
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'comment_id', onDelete: 'CASCADE')]
    private ?ListComment $comment = null;

    public function getComment(): ?ListComment { return $this->comment; }
    public function setComment(?ListComment $comment): static { $this->comment = $comment; return $this; }

    public function getTarget(): ?Reportable { return $this->comment; }

    public function getType(): string { return 'comment'; }
}
