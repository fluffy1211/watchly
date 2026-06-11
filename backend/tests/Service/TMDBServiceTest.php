<?php

namespace App\Tests\Service;

use App\Service\TMDBService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Psr\Log\NullLogger;

class TMDBServiceTest extends TestCase
{
    private function makeService(MockHttpClient $client): TMDBService
    {
        return new TMDBService($client, 'fake_key', new NullLogger());
    }

    public function testSearchMoviesReturnsResults(): void
    {
        $payload = ['results' => [['id' => 1, 'title' => 'Test Movie']]];
        $client = new MockHttpClient(new MockResponse(json_encode($payload)));

        $results = $this->makeService($client)->searchMovies('Test');

        $this->assertSame([['id' => 1, 'title' => 'Test Movie']], $results);
    }

    public function testSearchMoviesReturnsEmptyOnHttpError(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 500]));

        $results = $this->makeService($client)->searchMovies('Test');

        $this->assertSame([], $results);
    }

    public function testGetMovieByIdReturnsData(): void
    {
        $payload = ['id' => 42, 'title' => 'Inception'];
        $client = new MockHttpClient(new MockResponse(json_encode($payload)));

        $result = $this->makeService($client)->getMovieById(42);

        $this->assertSame(42, $result['id']);
    }

    public function testGetPopularReturnsResults(): void
    {
        $payload = ['results' => [['id' => 5, 'title' => 'Popular Film']], 'total_pages' => 3, 'page' => 1];
        $client = new MockHttpClient(new MockResponse(json_encode($payload)));

        $data = $this->makeService($client)->getPopular();

        $this->assertCount(1, $data['results']);
        $this->assertSame(3, $data['total_pages']);
        $this->assertSame(1, $data['page']);
    }
}
