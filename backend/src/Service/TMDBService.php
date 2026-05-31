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
        return $this->get('/search/movie', ['query' => $query])['results'] ?? [];
    }

    public function getMovieById(int $tmdbId): array
    {
        return $this->get(sprintf('/movie/%d', $tmdbId), ['append_to_response' => 'credits']);
    }

    public function getPopular(): array
    {
        return $this->get('/movie/popular')['results'] ?? [];
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
