<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $tmdbId = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backdropPath = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $runtime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $voteAverage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'films')]
    #[ORM\JoinTable(name: 'film_genre')]
    private Collection $genres;

    #[ORM\OneToMany(mappedBy: 'film', targetEntity: UserCollection::class, cascade: ['remove'])]
    private Collection $userCollections;

    #[ORM\OneToMany(mappedBy: 'film', targetEntity: Review::class, cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->userCollections = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTmdbId(): ?int { return $this->tmdbId; }
    public function setTmdbId(int $tmdbId): static { $this->tmdbId = $tmdbId; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getOriginalTitle(): ?string { return $this->originalTitle; }
    public function setOriginalTitle(?string $t): static { $this->originalTitle = $t; return $this; }

    public function getOverview(): ?string { return $this->overview; }
    public function setOverview(?string $o): static { $this->overview = $o; return $this; }

    public function getPosterPath(): ?string { return $this->posterPath; }
    public function setPosterPath(?string $p): static { $this->posterPath = $p; return $this; }

    public function getBackdropPath(): ?string { return $this->backdropPath; }
    public function setBackdropPath(?string $b): static { $this->backdropPath = $b; return $this; }

    public function getReleaseDate(): ?\DateTimeInterface { return $this->releaseDate; }
    public function setReleaseDate(?\DateTimeInterface $d): static { $this->releaseDate = $d; return $this; }

    public function getRuntime(): ?int { return $this->runtime; }
    public function setRuntime(?int $r): static { $this->runtime = $r; return $this; }

    public function getVoteAverage(): ?string { return $this->voteAverage; }
    public function setVoteAverage(?string $v): static { $this->voteAverage = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getGenres(): Collection { return $this->genres; }
    public function addGenre(Genre $genre): static { if (!$this->genres->contains($genre)) { $this->genres->add($genre); } return $this; }
    public function removeGenre(Genre $genre): static { $this->genres->removeElement($genre); return $this; }

    public function getUserCollections(): Collection { return $this->userCollections; }
    public function getReviews(): Collection { return $this->reviews; }
}
