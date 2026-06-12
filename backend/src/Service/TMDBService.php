<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TMDBService
{
    private const BASE_URL = 'https://api.themoviedb.org/3';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $tmdbApiKey,
        private readonly LoggerInterface $logger,
    ) {}

    public function searchMovies(string $query): array
    {
        $response = $this->get('/search/movie', ['query' => $query]);
        return [
            'results'       => $response['results'] ?? [],
            'total_results' => $response['total_results'] ?? 0,
        ];
    }

    public function getMovieById(int $tmdbId): array
    {
        return $this->get(sprintf('/movie/%d', $tmdbId), ['append_to_response' => 'credits']);
    }

    public function getPopular(int $page = 1): array
    {
        $response = $this->get('/movie/popular', ['page' => $page]);
        return [
            'results'     => $response['results'] ?? [],
            'total_pages' => $response['total_pages'] ?? 1,
            'page'        => $response['page'] ?? $page,
        ];
    }

    public function getGenres(): array
    {
        return $this->get('/genre/movie/list')['genres'] ?? [];
    }

    public function discoverByGenre(int $genreId, int $page = 1): array
    {
        $response = $this->get('/discover/movie', [
            'with_genres' => $genreId,
            'sort_by'     => 'popularity.desc',
            'page'        => $page,
        ]);
        return [
            'results'     => $response['results'] ?? [],
            'total_pages' => $response['total_pages'] ?? 1,
            'page'        => $response['page'] ?? $page,
        ];
    }

    private function get(string $path, array $extra = []): array
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL . $path, [
                'query' => array_merge(['language' => 'fr-FR', 'api_key' => $this->tmdbApiKey], $extra),
            ]);
            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('TMDB HTTP error: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            $this->logger->error('TMDB error: ' . $e->getMessage());
            return [];
        }
    }
}
