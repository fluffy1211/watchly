<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\TMDBService;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CollectionControllerTest extends BaseWebTestCase
{
    private static array $fakeTmdbMovie = [
        'id'             => 27205,
        'title'          => 'Inception',
        'original_title' => 'Inception',
        'overview'       => 'A mind-bending thriller.',
        'poster_path'    => '/poster.jpg',
        'backdrop_path'  => '/backdrop.jpg',
        'release_date'   => '2010-07-16',
        'runtime'        => 148,
        'vote_average'   => 8.4,
        'genres'         => [['id' => 28, 'name' => 'Action']],
        'credits'        => ['cast' => []],
    ];

    private function createUserWithToken(string $email, string $username): array
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user   = new User();
        $user->setEmail($email)->setUsername($username)->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $token = static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);

        return ['user' => $user, 'token' => $token];
    }

    private function mockTmdb(array $returnMap): void
    {
        $mock = $this->createMock(TMDBService::class);
        foreach ($returnMap as $method => $return) {
            $mock->method($method)->willReturn($return);
        }
        static::getContainer()->set(TMDBService::class, $mock);
    }

    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'];
    }

    private function addFilmToCollection(string $token): array
    {
        $this->mockTmdb(['getMovieById' => self::$fakeTmdbMovie]);

        $this->client->request('POST', '/api/collection/add', [], [], $this->authHeaders($token),
            json_encode(['tmdb_id' => 27205]));

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    // --- Tests ---

    public function testAddFilmToCollection(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');

        $data = $this->addFilmToCollection($token);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('Film added to collection', $data['message']);
        $this->assertSame('WATCHLIST', $data['collection']['status']);
        $this->assertSame('Inception', $data['collection']['film']['title']);
    }

    public function testAddDuplicateFilm(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $this->addFilmToCollection($token);

        // Mock already set by addFilmToCollection — cannot re-set after service is initialized
        $this->client->request('POST', '/api/collection/add', [], [], $this->authHeaders($token),
            json_encode(['tmdb_id' => 27205]));

        $this->assertResponseStatusCodeSame(409);
    }

    public function testGetCollection(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $this->addFilmToCollection($token);

        $this->client->request('GET', '/api/collection', [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame('Inception', $data[0]['film']['title']);
    }

    public function testFilterByStatus(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $this->addFilmToCollection($token);

        $this->client->request('GET', '/api/collection?status=WATCHLIST', [], [], $this->authHeaders($token));
        $this->assertResponseStatusCodeSame(200);
        $watchlist = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $watchlist);

        $this->client->request('GET', '/api/collection?status=WATCHED', [], [], $this->authHeaders($token));
        $this->assertResponseStatusCodeSame(200);
        $watched = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $watched);
    }

    public function testUpdateStatusToWatched(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/status", [], [], $this->authHeaders($token),
            json_encode(['status' => 'WATCHED']));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('WATCHED', $data['collection']['status']);
        $this->assertNotNull($data['collection']['watched_at']);
    }

    public function testCannotFavoriteWatchlistFilm(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/favorite", [], [], $this->authHeaders($token),
            json_encode(['is_favorite' => true]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testFavoriteWatchedFilm(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/status", [], [], $this->authHeaders($token),
            json_encode(['status' => 'WATCHED']));

        $this->client->request('PATCH', "/api/collection/{$id}/favorite", [], [], $this->authHeaders($token),
            json_encode(['is_favorite' => true]));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['collection']['is_favorite']);
    }

    public function testCannotRateWatchlistFilm(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/rating", [], [], $this->authHeaders($token),
            json_encode(['rating' => 4]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRateWatchedFilm(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/status", [], [], $this->authHeaders($token),
            json_encode(['status' => 'WATCHED']));

        $this->client->request('PATCH', "/api/collection/{$id}/rating", [], [], $this->authHeaders($token),
            json_encode(['rating' => 4]));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(4, $data['collection']['rating']);
    }

    public function testDeleteFromCollection(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $added = $this->addFilmToCollection($token);
        $id    = $added['collection']['id'];

        $this->client->request('DELETE', "/api/collection/{$id}", [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(204);
    }

    public function testCannotAccessOtherUserCollection(): void
    {
        ['token' => $token1] = $this->createUserWithToken('user1@test.com', 'user1');
        ['token' => $token2] = $this->createUserWithToken('user2@test.com', 'user2');

        $added = $this->addFilmToCollection($token1);
        $id    = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$id}/status", [], [], $this->authHeaders($token2),
            json_encode(['status' => 'WATCHED']));

        $this->assertResponseStatusCodeSame(403);
    }
}
