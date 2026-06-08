<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\TMDBService;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FilmControllerTest extends BaseWebTestCase
{
    private function getAuthToken(): string
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('film@example.com');
        $user->setUsername('filmuser');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'password123'));

        $this->em->persist($user);
        $this->em->flush();

        return static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
    }

    private function mockTmdb(array $returnMap): void
    {
        $mock = $this->createStub(TMDBService::class);
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

        $this->client->request('GET', '/api/films/search', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testSearchSuccess(): void
    {
        $token = $this->getAuthToken();

        $this->mockTmdb([
            'searchMovies' => [[
                'id' => 27205, 'title' => 'Inception', 'original_title' => 'Inception',
                'release_date' => '2010-07-16', 'poster_path' => '/poster.jpg',
                'vote_average' => 8.4, 'overview' => 'A thief who steals corporate secrets.',
            ]],
        ]);

        $this->client->request('GET', '/api/films/search?q=inception', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);

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
            'getPopular' => [[
                'id' => 550, 'title' => 'Fight Club', 'original_title' => 'Fight Club',
                'release_date' => '1999-10-15', 'poster_path' => '/poster.jpg',
                'vote_average' => 8.8, 'overview' => 'An insomniac office worker.',
            ]],
        ]);

        $this->client->request('GET', '/api/films/popular', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);

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

        $this->client->request('GET', '/api/films/99999999', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
