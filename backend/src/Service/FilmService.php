<?php

namespace App\Service;

use App\Entity\Film;
use App\Entity\Genre;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;

class FilmService
{
    public function __construct(
        private readonly FilmRepository $filmRepository,
        private readonly GenreRepository $genreRepository,
    ) {}

    public function findOrCreate(array $tmdbData, EntityManagerInterface $em): Film
    {
        $film = $this->filmRepository->findOneBy(['tmdbId' => $tmdbData['id']]);

        if ($film !== null) {
            return $film;
        }

        $film = new Film();
        $film->setTmdbId($tmdbData['id']);
        $film->setTitle($tmdbData['title'] ?? '');
        $film->setOriginalTitle($tmdbData['original_title'] ?? null);
        $film->setOverview($tmdbData['overview'] ?? null);
        $film->setPosterPath($tmdbData['poster_path'] ?? null);
        $film->setBackdropPath($tmdbData['backdrop_path'] ?? null);
        $film->setRuntime($tmdbData['runtime'] ?? null);
        $film->setVoteAverage(isset($tmdbData['vote_average']) ? (string) $tmdbData['vote_average'] : null);

        if (!empty($tmdbData['release_date'])) {
            $film->setReleaseDate(new \DateTime($tmdbData['release_date']));
        }

        $em->persist($film);
        $em->flush();

        return $film;
    }

    public function syncGenres(Film $film, array $tmdbGenres, EntityManagerInterface $em): void
    {
        foreach ($tmdbGenres as $tmdbGenre) {
            $genre = $this->genreRepository->findOneBy(['tmdbId' => $tmdbGenre['id']]);

            if ($genre === null) {
                $genre = new Genre();
                $genre->setTmdbId($tmdbGenre['id']);
                $genre->setName($tmdbGenre['name']);
                $em->persist($genre);
            }

            $film->addGenre($genre);
        }

        $em->flush();
    }
}
