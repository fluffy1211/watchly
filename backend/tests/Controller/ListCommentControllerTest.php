<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ListCommentControllerTest extends BaseWebTestCase
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

    private function createList(string $token, string $title, string $visibility = 'PUBLIC'): array
    {
        $this->client->request('POST', '/api/lists', [], [], $this->authHeaders($token),
            json_encode(['title' => $title, 'visibility' => $visibility]));
        return json_decode($this->client->getResponse()->getContent(), true)['list'];
    }

    public function testAddCommentOnPublicList(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($otherToken),
            json_encode(['content' => 'Great picks!']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Great picks!', $data['comment']['content']);
        $this->assertSame('other', $data['comment']['author']['username']);
    }

    public function testCommentOnPrivateListDeniedForNonOwner(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Private List', 'PRIVATE');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($otherToken),
            json_encode(['content' => 'Trying to comment']));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCommentRequiresAuth(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['content' => 'No auth']));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testEmptyCommentRejected(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => '   ']));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testListCommentsPublic(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => 'First comment']));

        $this->client->request('GET', "/api/lists/{$list['id']}/comments");

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
    }

    public function testDeleteOwnComment(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($otherToken),
            json_encode(['content' => 'To be deleted']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('DELETE', "/api/comments/{$comment['id']}", [], [], $this->authHeaders($otherToken));

        $this->assertResponseStatusCodeSame(204);
    }

    public function testListOwnerCannotDeleteOthersComment(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($otherToken),
            json_encode(['content' => 'Not yours to delete']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('DELETE', "/api/comments/{$comment['id']}", [], [], $this->authHeaders($ownerToken));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testReportComment(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => 'Reportable comment']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('POST', "/api/comments/{$comment['id']}/report", [], [], $this->authHeaders($otherToken),
            json_encode(['reason' => 'Spam']));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testCannotReportOwnComment(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => 'My own comment']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('POST', "/api/comments/{$comment['id']}/report", [], [], $this->authHeaders($ownerToken),
            json_encode(['reason' => 'Spam']));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCannotReportSameCommentTwice(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        ['token' => $otherToken] = $this->createUserWithToken('other@test.com', 'other');

        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => 'Reportable comment']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('POST', "/api/comments/{$comment['id']}/report", [], [], $this->authHeaders($otherToken),
            json_encode(['reason' => 'Spam']));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', "/api/comments/{$comment['id']}/report", [], [], $this->authHeaders($otherToken),
            json_encode(['reason' => 'Spam again']));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testReportCommentRequiresAuth(): void
    {
        ['token' => $ownerToken] = $this->createUserWithToken('owner@test.com', 'owner');
        $list = $this->createList($ownerToken, 'Public List', 'PUBLIC');

        $this->client->request('POST', "/api/lists/{$list['id']}/comments", [], [], $this->authHeaders($ownerToken),
            json_encode(['content' => 'Reportable comment']));
        $comment = json_decode($this->client->getResponse()->getContent(), true)['comment'];

        $this->client->request('POST', "/api/comments/{$comment['id']}/report", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['reason' => 'Spam']));

        $this->assertResponseStatusCodeSame(401);
    }
}
