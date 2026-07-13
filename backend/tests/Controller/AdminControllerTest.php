<?php

namespace App\Tests\Controller;

use App\Entity\CommentReport;
use App\Entity\Film;
use App\Entity\ListComment;
use App\Entity\MovieList;
use App\Entity\User;
use App\Entity\UserCollection;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminControllerTest extends BaseWebTestCase
{
    private function createUser(string $email, string $username, array $roles = ['ROLE_USER']): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user   = new User();
        $user->setEmail($email)->setUsername($username)->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    private function tokenFor(User $user): string
    {
        return static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
    }

    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'];
    }

    public function testGetUsersRequiresAdmin(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request('GET', '/api/admin/users', [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetUsersSuccess(): void
    {
        $this->createUser('user@test.com', 'regularuser');
        $admin = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token = $this->tokenFor($admin);

        $this->client->request('GET', '/api/admin/users', [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function testGetUsersContainsStats(): void
    {
        $admin = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token = $this->tokenFor($admin);

        $this->client->request('GET', '/api/admin/users', [], [], $this->authHeaders($token));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('stats', $data[0]);
        $this->assertArrayHasKey('total_films', $data[0]['stats']);
        $this->assertArrayHasKey('watched', $data[0]['stats']);
        $this->assertArrayHasKey('favorites', $data[0]['stats']);
    }

    public function testPromoteUser(): void
    {
        $target     = $this->createUser('user@test.com', 'regularuser');
        $superAdmin = $this->createUser('super@test.com', 'superadmin', ['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        $token      = $this->tokenFor($superAdmin);

        $this->client->request(
            'PATCH',
            '/api/admin/users/' . $target->getId(),
            [], [],
            $this->authHeaders($token),
            json_encode(['roles' => ['ROLE_USER', 'ROLE_ADMIN']])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('ROLE_ADMIN', $data['user']['roles']);
    }

    public function testRegularAdminCannotPromoteUser(): void
    {
        $target = $this->createUser('user2@test.com', 'regularuser2');
        $admin  = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token  = $this->tokenFor($admin);

        $this->client->request(
            'PATCH',
            '/api/admin/users/' . $target->getId(),
            [], [],
            $this->authHeaders($token),
            json_encode(['roles' => ['ROLE_USER', 'ROLE_ADMIN']])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCannotModifyOwnAccount(): void
    {
        $admin = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token = $this->tokenFor($admin);

        $this->client->request(
            'PATCH',
            '/api/admin/users/' . $admin->getId(),
            [], [],
            $this->authHeaders($token),
            json_encode(['roles' => ['ROLE_USER', 'ROLE_ADMIN']])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteUser(): void
    {
        $target = $this->createUser('user@test.com', 'regularuser');
        $admin  = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token  = $this->tokenFor($admin);

        $this->client->request('DELETE', '/api/admin/users/' . $target->getId(), [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(204);
    }

    public function testCannotDeleteOwnAccount(): void
    {
        $admin = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token = $this->tokenFor($admin);

        $this->client->request('DELETE', '/api/admin/users/' . $admin->getId(), [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteCascadesCollection(): void
    {
        $target = $this->createUser('user@test.com', 'regularuser');
        $admin  = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);

        $film = new Film();
        $film->setTmdbId(rand(900000, 999999))->setTitle('Test Film');
        $this->em->persist($film);

        $uc = new UserCollection();
        $uc->setUser($target)->setFilm($film);
        $this->em->persist($uc);
        $this->em->flush();

        $token = $this->tokenFor($admin);
        $this->client->request('DELETE', '/api/admin/users/' . $target->getId(), [], [], $this->authHeaders($token));
        $this->assertResponseStatusCodeSame(204);

        $this->em->clear();
        $remaining = $this->em->getRepository(UserCollection::class)->findBy(['user' => $target]);
        $this->assertCount(0, $remaining);
    }

    private function createReportedComment(User $author, User $reporter): CommentReport
    {
        $list = new MovieList();
        $list->setOwner($author)->setTitle('Test List')->setVisibility('PUBLIC');
        $this->em->persist($list);

        $comment = new ListComment();
        $comment->setList($list)->setAuthor($author)->setContent('Reported content');
        $this->em->persist($comment);

        $report = new CommentReport();
        $report->setComment($comment)->setReporter($reporter)->setReason('Spam');
        $this->em->persist($report);

        $this->em->flush();

        return $report;
    }

    public function testGetCommentReportsRequiresAdmin(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request('GET', '/api/admin/comment-reports', [], [], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetCommentReportsSuccess(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $this->createReportedComment($author, $reporter);

        $this->client->request('GET', '/api/admin/comment-reports', [], [], $this->authHeaders($this->tokenFor($admin)));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame('reporter', $data[0]['reporter']['username']);
        $this->assertSame('author', $data[0]['comment']['author']['username']);
    }

    public function testKeepCommentReportDismissesReportOnly(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $report    = $this->createReportedComment($author, $reporter);
        $reportId  = $report->getId();
        $commentId = $report->getComment()->getId();

        $this->client->request(
            'PATCH',
            '/api/admin/comment-reports/' . $reportId,
            [], [],
            $this->authHeaders($this->tokenFor($admin)),
            json_encode(['action' => 'keep'])
        );

        $this->assertResponseStatusCodeSame(200);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(CommentReport::class)->find($reportId));
        $this->assertNotNull($this->em->getRepository(ListComment::class)->find($commentId));
    }

    public function testDeleteCommentReportRemovesCommentAndReport(): void
    {
        $author   = $this->createUser('author@test.com', 'author');
        $reporter = $this->createUser('reporter@test.com', 'reporter');
        $admin    = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $report    = $this->createReportedComment($author, $reporter);
        $reportId  = $report->getId();
        $commentId = $report->getComment()->getId();

        $this->client->request(
            'PATCH',
            '/api/admin/comment-reports/' . $reportId,
            [], [],
            $this->authHeaders($this->tokenFor($admin)),
            json_encode(['action' => 'delete'])
        );

        $this->assertResponseStatusCodeSame(200);
        $this->em->clear();
        $this->assertNull($this->em->getRepository(CommentReport::class)->find($reportId));
        $this->assertNull($this->em->getRepository(ListComment::class)->find($commentId));
    }
}
