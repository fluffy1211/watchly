<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\TMDBService;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReviewControllerTest extends BaseWebTestCase
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

    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'];
    }

    private function mockTmdb(): void
    {
        $mock = $this->createStub(TMDBService::class);
        $mock->method('getMovieById')->willReturn(self::$fakeTmdbMovie);
        static::getContainer()->set(TMDBService::class, $mock);
    }

    private function setupWatchedFilm(string $token): array
    {
        $this->mockTmdb();

        $this->client->request('POST', '/api/collection/add', [], [], $this->authHeaders($token),
            json_encode(['tmdb_id' => 27205]));
        $added        = json_decode($this->client->getResponse()->getContent(), true);
        $collectionId = $added['collection']['id'];

        $this->client->request('PATCH', "/api/collection/{$collectionId}/status", [], [], $this->authHeaders($token),
            json_encode(['status' => 'WATCHED']));

        return ['tmdb_id' => 27205, 'collection_id' => $collectionId];
    }

    // --- Tests ---

    public function testCreateReviewRequiresWatched(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        $this->mockTmdb();

        $this->client->request('POST', '/api/collection/add', [], [], $this->authHeaders($token),
            json_encode(['tmdb_id' => 27205]));

        $this->client->request('PUT', '/api/films/27205/review', [], [], $this->authHeaders($token),
            json_encode(['content' => 'This is a great film worth watching.']));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateReviewSuccess(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'An absolutely mind-bending experience.']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Review saved', $data['message']);
        $this->assertArrayHasKey('id', $data['review']);
    }

    public function testUpdateReview(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'First version of this review.']));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'Updated version, much better now.']));
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Updated version, much better now.', $data['review']['content']);
    }

    public function testReviewTooShort(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'Short']));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetReviewsSuccess(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'A wonderful cinematic experience indeed.']));

        $this->client->request('GET', "/api/films/{$tmdbId}/reviews", [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('testuser', $data[0]['user']['username']);
    }

    public function testGetReviewsPublic(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'Public reviews should be accessible to all.']));

        $this->client->request('GET', "/api/films/{$tmdbId}/reviews");

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testDeleteReview(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');
        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token),
            json_encode(['content' => 'This review will be deleted soon enough.']));

        $this->client->request('DELETE', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(204);
    }

    public function testCannotDeleteOtherUserReview(): void
    {
        ['token' => $token1] = $this->createUserWithToken('user1@test.com', 'user1');
        ['token' => $token2] = $this->createUserWithToken('user2@test.com', 'user2');

        ['tmdb_id' => $tmdbId] = $this->setupWatchedFilm($token1);

        $this->client->request('PUT', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token1),
            json_encode(['content' => 'User one wrote this review for the film.']));

        $this->client->request('DELETE', "/api/films/{$tmdbId}/review", [], [], $this->authHeaders($token2));

        $this->assertResponseStatusCodeSame(404);
    }
}
