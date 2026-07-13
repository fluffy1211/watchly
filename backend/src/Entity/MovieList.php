<?php

namespace App\Entity;

use App\Repository\MovieListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieListRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MovieList
{
    public const VISIBILITY_PUBLIC  = 'PUBLIC';
    public const VISIBILITY_PRIVATE = 'PRIVATE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'movieLists')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(columnDefinition: "ENUM('PUBLIC', 'PRIVATE') NOT NULL")]
    private string $visibility = self::VISIBILITY_PUBLIC;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'list', targetEntity: ListFilm::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $listFilms;

    #[ORM\OneToMany(mappedBy: 'list', targetEntity: ListComment::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->listFilms = new ArrayCollection();
        $this->comments  = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): static { $this->owner = $owner; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getVisibility(): string { return $this->visibility; }
    public function setVisibility(string $visibility): static { $this->visibility = $visibility; return $this; }

    public function isPublic(): bool { return $this->visibility === self::VISIBILITY_PUBLIC; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getListFilms(): Collection { return $this->listFilms; }
    public function getComments(): Collection { return $this->comments; }
}
