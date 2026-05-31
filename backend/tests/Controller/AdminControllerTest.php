<?php

namespace App\Tests\Controller;

use App\Entity\Film;
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
        $target = $this->createUser('user@test.com', 'regularuser');
        $admin  = $this->createUser('admin@test.com', 'adminuser', ['ROLE_USER', 'ROLE_ADMIN']);
        $token  = $this->tokenFor($admin);

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
}
