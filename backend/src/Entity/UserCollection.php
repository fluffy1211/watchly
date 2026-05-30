<?php

namespace App\Entity;

use App\Repository\UserCollectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCollectionRepository::class)]
#[ORM\UniqueConstraint(name: 'uq_user_film', columns: ['user_id', 'film_id'])]
class UserCollection
{
    public const STATUS_WATCHLIST = 'WATCHLIST';
    public const STATUS_WATCHED   = 'WATCHED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCollections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userCollections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Film $film = null;

    #[ORM\Column(columnDefinition: "ENUM('WATCHLIST', 'WATCHED') NOT NULL")]
    private string $status = self::STATUS_WATCHLIST;

    #[ORM\Column]
    private bool $isFavorite = false;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $rating = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $watchedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getFilm(): ?Film { return $this->film; }
    public function setFilm(?Film $film): static { $this->film = $film; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function isWatched(): bool { return $this->status === self::STATUS_WATCHED; }
    public function canBeFavorite(): bool { return $this->isWatched(); }
    public function canBeRated(): bool { return $this->isWatched(); }

    public function isFavorite(): bool { return $this->isFavorite; }
    public function setIsFavorite(bool $v): static { $this->isFavorite = $v; return $this; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(?int $rating): static { $this->rating = $rating; return $this; }

    public function getAddedAt(): ?\DateTimeImmutable { return $this->addedAt; }

    public function getWatchedAt(): ?\DateTimeImmutable { return $this->watchedAt; }
    public function setWatchedAt(?\DateTimeImmutable $w): static { $this->watchedAt = $w; return $this; }
}
