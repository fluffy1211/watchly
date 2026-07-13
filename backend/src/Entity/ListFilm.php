<?php

namespace App\Entity;

use App\Repository\ListFilmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListFilmRepository::class)]
#[ORM\UniqueConstraint(name: 'uq_list_film', columns: ['list_id', 'film_id'])]
class ListFilm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'list_id', nullable: false, onDelete: 'CASCADE')]
    private ?MovieList $list = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Film $film = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getList(): ?MovieList { return $this->list; }
    public function setList(?MovieList $list): static { $this->list = $list; return $this; }

    public function getFilm(): ?Film { return $this->film; }
    public function setFilm(?Film $film): static { $this->film = $film; return $this; }

    public function getAddedAt(): ?\DateTimeImmutable { return $this->addedAt; }
}
