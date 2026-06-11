<?php

namespace App\Controller;

use App\Service\FilmService;
use App\Service\TMDBService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FilmController extends AbstractController
{
    #[Route('/api/films/search', name: 'api_films_search', methods: ['GET'])]
    public function search(Request $request, TMDBService $tmdb): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));

        if ($query === '') {
            return $this->json(['message' => 'Missing query parameter "q"'], Response::HTTP_BAD_REQUEST);
        }

        $results = $tmdb->searchMovies($query);

        return $this->json(array_map(fn($m) => $this->formatSearchResult($m), $results));
    }

    #[Route('/api/films/popular', name: 'api_films_popular', methods: ['GET'])]
    public function popular(Request $request, TMDBService $tmdb): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $data = $tmdb->getPopular($page);

        return $this->json([
            'films'       => array_map(fn($m) => $this->formatSearchResult($m), $data['results']),
            'page'        => $data['page'],
            'total_pages' => $data['total_pages'],
        ]);
    }

    #[Route('/api/films/genres', name: 'api_films_genres', methods: ['GET'])]
    public function genres(TMDBService $tmdb): JsonResponse
    {
        return $this->json(['genres' => $tmdb->getGenres()]);
    }

    #[Route('/api/films/discover', name: 'api_films_discover', methods: ['GET'])]
    public function discover(Request $request, TMDBService $tmdb): JsonResponse
    {
        $genreId = (int) $request->query->get('genre_id', 0);
        $page    = max(1, (int) $request->query->get('page', 1));

        if ($genreId <= 0) {
            return $this->json(['message' => 'Missing or invalid genre_id'], Response::HTTP_BAD_REQUEST);
        }

        $data = $tmdb->discoverByGenre($genreId, $page);

        return $this->json([
            'films'       => array_map(fn($m) => $this->formatSearchResult($m), $data['results']),
            'page'        => $data['page'],
            'total_pages' => $data['total_pages'],
        ]);
    }

    #[Route('/api/films/{id}', name: 'api_films_detail', methods: ['GET'])]
    public function detail(int $id, TMDBService $tmdb): JsonResponse
    {
        $data = $tmdb->getMovieById($id);

        if (empty($data)) {
            return $this->json(['message' => 'Film not found'], Response::HTTP_NOT_FOUND);
        }

        $genres = array_map(fn($g) => ['id' => $g['id'], 'name' => $g['name']], $data['genres'] ?? []);
        $cast = array_slice(
            array_map(fn($c) => ['name' => $c['name'], 'character' => $c['character'] ?? null, 'profile_path' => $c['profile_path'] ?? null], $data['credits']['cast'] ?? []),
            0,
            10
        );

        return $this->json([
            'tmdb_id'        => $data['id'],
            'title'          => $data['title'] ?? null,
            'original_title' => $data['original_title'] ?? null,
            'overview'       => $data['overview'] ?? null,
            'poster_path'    => $data['poster_path'] ?? null,
            'backdrop_path'  => $data['backdrop_path'] ?? null,
            'release_date'   => $data['release_date'] ?? null,
            'runtime'        => $data['runtime'] ?? null,
            'vote_average'   => $data['vote_average'] ?? null,
            'genres'         => $genres,
            'cast'           => $cast,
        ]);
    }

    private function formatSearchResult(array $m): array
    {
        return [
            'tmdb_id'        => $m['id'],
            'title'          => $m['title'] ?? null,
            'original_title' => $m['original_title'] ?? null,
            'release_date'   => $m['release_date'] ?? null,
            'poster_path'    => $m['poster_path'] ?? null,
            'vote_average'   => $m['vote_average'] ?? null,
            'overview'       => $m['overview'] ?? null,
        ];
    }
}
