<?php

namespace App\Tests\Controller;

use App\Service\TMDBService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FilmControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    private function getAuthToken(): string
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'film@example.com', 'password' => 'password123', 'username' => 'filmuser'])
        );

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'film@example.com', 'password' => 'password123'])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        return $data['token'];
    }

    private function mockTmdb(array $returnMap): void
    {
        $mock = $this->createMock(TMDBService::class);

        foreach ($returnMap as $method => $return) {
            $mock->method($method)->willReturn($return);
        }

        static::getContainer()->set(TMDBService::class, $mock);
    }

    public function testSearchRequiresAuth(): void
    {
        $this->client->request('GET', '/api/films/search?q=inception');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testSearchMissingQuery(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/films/search',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testSearchSuccess(): void
    {
        $token = $this->getAuthToken();

        $this->mockTmdb([
            'searchMovies' => [
                [
                    'id' => 27205,
                    'title' => 'Inception',
                    'original_title' => 'Inception',
                    'release_date' => '2010-07-16',
                    'poster_path' => '/poster.jpg',
                    'vote_average' => 8.4,
                    'overview' => 'A thief who steals corporate secrets.',
                ],
            ],
        ]);

        $this->client->request(
            'GET',
            '/api/films/search?q=inception',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame(27205, $data[0]['tmdb_id']);
        $this->assertSame('Inception', $data[0]['title']);
    }

    public function testGetPopularSuccess(): void
    {
        $token = $this->getAuthToken();

        $this->mockTmdb([
            'getPopular' => [
                [
                    'id' => 550,
                    'title' => 'Fight Club',
                    'original_title' => 'Fight Club',
                    'release_date' => '1999-10-15',
                    'poster_path' => '/poster.jpg',
                    'vote_average' => 8.8,
                    'overview' => 'An insomniac office worker.',
                ],
            ],
        ]);

        $this->client->request(
            'GET',
            '/api/films/popular',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame(550, $data[0]['tmdb_id']);
    }

    public function testGetFilmByIdNotFound(): void
    {
        $token = $this->getAuthToken();

        $this->mockTmdb(['getMovieById' => []]);

        $this->client->request(
            'GET',
            '/api/films/99999999',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
