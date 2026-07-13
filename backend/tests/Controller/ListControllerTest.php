<?php

namespace App\Tests\Controller;

use App\Entity\Film;
use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ListControllerTest extends BaseWebTestCase
{
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

    private function createFilm(int $tmdbId, string $title): Film
    {
        $film = new Film();
        $film->setTmdbId($tmdbId)->setTitle($title);
        $this->em->persist($film);
        $this->em->flush();
        return $film;
    }

    private function createList(string $token, string $title, string $visibility = 'PUBLIC'): array
    {
        $this->client->request('POST', '/api/lists', [], [], $this->authHeaders($token),
            json_encode(['title' => $title, 'visibility' => $visibility]));
        return json_decode($this->client->getResponse()->getContent(), true)['list'];
    }

    // --- Create ---

    public function testCreateListSuccess(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');

        $this->client->request('POST', '/api/lists', [], [], $this->authHeaders($token),
            json_encode(['title' => 'Best Sci-Fi', 'description' => 'My favorites', 'visibility' => 'PUBLIC']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Best Sci-Fi', $data['list']['title']);
        $this->assertSame('PUBLIC', $data['list']['visibility']);
    }

    public function testCreateListRequiresTitle(): void
    {
        ['token' => $token] = $this->createUserWithToken('user@test.com', 'testuser');

        $this->client->request('POST', '/api/lists', [], [], $this->authHeaders($token),
            json_encode(['title' => '']));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateListRequiresAuth(): void
    {
        $this->client->request('POST', '/api/lists', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'No auth list']));

        $this->assertResponseStatusCodeSame(401);
    }

    // --- Browse ---

    public function testBrowsePublicListsOnly(): void
    {
        ['token' => $token1] = $this->createUserWithToken('user1@test.com', 'user1');
        ['token' => $token2] = $this->createUserWithToken('user2@test.com', 'user2');

        $this->createList($token1, 'Public List', 'PUBLIC');
        $this->createList($token2, 'Private List', 'PRIVATE');

        $this->client->request('GET', '/api/lists');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('Public List', $data[0]['title']);
    }

    public function testBrowseSearchByTitleOrOwner(): void
    {
        ['token' => $token1] = $this->createUserWithToken('user1@test.com', 'moviebuff');
        $this->createList($token1, 'Horror Classics', 'PUBLIC');

        $this->client->request('GET', '/api/lists?q=moviebuff');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);

        $this->client->request('GET', '/api/lists?q=nomatch');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $data);
    }

    // --- Show / visibility ---

    public function testShowPrivateListDeniedForNonOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Secret List', 'PRIVATE');

        $this->client->request('GET', "/api/lists/{$list['id']}", [], [], $this->authHeaders($otherToken));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowPrivateListAllowedForOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');

        $list = $this->createList($ownerToken, 'Secret List', 'PRIVATE');

        $this->client->request('GET', "/api/lists/{$list['id']}", [], [], $this->authHeaders($ownerToken));

        $this->assertResponseStatusCodeSame(200);
    }

    // --- Update ---

    public function testUpdateListDeniedForNonOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Original Title', 'PUBLIC');

        $this->client->request('PATCH', "/api/lists/{$list['id']}", [], [], $this->authHeaders($otherToken),
            json_encode(['title' => 'Hijacked Title']));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateListSuccess(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');

        $list = $this->createList($ownerToken, 'Original Title', 'PUBLIC');

        $this->client->request('PATCH', "/api/lists/{$list['id']}", [], [], $this->authHeaders($ownerToken),
            json_encode(['title' => 'Updated Title', 'visibility' => 'PRIVATE']));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Updated Title', $data['list']['title']);
        $this->assertSame('PRIVATE', $data['list']['visibility']);
    }

    // --- Delete ---

    public function testDeleteListDeniedForNonOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'To Delete', 'PUBLIC');

        $this->client->request('DELETE', "/api/lists/{$list['id']}", [], [], $this->authHeaders($otherToken));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteListSuccess(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');

        $list = $this->createList($ownerToken, 'To Delete', 'PUBLIC');

        $this->client->request('DELETE', "/api/lists/{$list['id']}", [], [], $this->authHeaders($ownerToken));

        $this->assertResponseStatusCodeSame(204);
    }

    // --- Films ---

    public function testAddFilmToListDeniedForNonOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'My List', 'PUBLIC');
        $this->createFilm(27205, 'Inception');

        $this->client->request('POST', "/api/lists/{$list['id']}/films/27205", [], [], $this->authHeaders($otherToken));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddAndRemoveFilmFromList(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');

        $list = $this->createList($ownerToken, 'My List', 'PUBLIC');
        $this->createFilm(27205, 'Inception');

        $this->client->request('POST', "/api/lists/{$list['id']}/films/27205", [], [], $this->authHeaders($ownerToken));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', "/api/lists/{$list['id']}", [], [], $this->authHeaders($ownerToken));
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data['films']);
        $this->assertSame(27205, $data['films'][0]['tmdb_id']);

        $this->client->request('DELETE', "/api/lists/{$list['id']}/films/27205", [], [], $this->authHeaders($ownerToken));
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', "/api/lists/{$list['id']}", [], [], $this->authHeaders($ownerToken));
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $data['films']);
    }

    public function testAddSameFilmTwiceConflicts(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');

        $list = $this->createList($ownerToken, 'My List', 'PUBLIC');
        $this->createFilm(27205, 'Inception');

        $this->client->request('POST', "/api/lists/{$list['id']}/films/27205", [], [], $this->authHeaders($ownerToken));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', "/api/lists/{$list['id']}/films/27205", [], [], $this->authHeaders($ownerToken));
        $this->assertResponseStatusCodeSame(409);
    }
}
