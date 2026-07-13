<?php

namespace App\Tests\Controller;

use App\Entity\Film;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserCollection;
use App\Tests\BaseWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends BaseWebTestCase
{
    private function createUser(string $email, string $username): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user   = new User();
        $user->setEmail($email)->setUsername($username)->setRoles(['ROLE_USER']);
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

    public function testChangePasswordSuccess(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request(
            'PUT',
            '/api/profile/password',
            [], [],
            $this->authHeaders($token),
            json_encode([
                'current_password' => 'password123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456',
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'POST',
            '/api/login',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'user@test.com', 'password' => 'newpassword456'])
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testChangePasswordWrongCurrentPassword(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request(
            'PUT',
            '/api/profile/password',
            [], [],
            $this->authHeaders($token),
            json_encode([
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456',
            ])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testChangePasswordMismatchConfirmation(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request(
            'PUT',
            '/api/profile/password',
            [], [],
            $this->authHeaders($token),
            json_encode([
                'current_password' => 'password123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'somethingelse',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testChangePasswordTooShort(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request(
            'PUT',
            '/api/profile/password',
            [], [],
            $this->authHeaders($token),
            json_encode([
                'current_password' => 'password123',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testChangePasswordRequiresAuth(): void
    {
        $this->client->request(
            'PUT',
            '/api/profile/password',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'current_password' => 'password123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456',
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteAccountSuccess(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $id    = $user->getId();
        $token = $this->tokenFor($user);

        $this->client->request(
            'DELETE',
            '/api/profile',
            [], [],
            $this->authHeaders($token),
            json_encode(['password' => 'password123'])
        );

        $this->assertResponseStatusCodeSame(204);

        $this->em->clear();
        $this->assertNull($this->em->getRepository(User::class)->find($id));
    }

    public function testDeleteAccountWrongPassword(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $id    = $user->getId();
        $token = $this->tokenFor($user);

        $this->client->request(
            'DELETE',
            '/api/profile',
            [], [],
            $this->authHeaders($token),
            json_encode(['password' => 'wrongpassword'])
        );

        $this->assertResponseStatusCodeSame(403);

        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(User::class)->find($id));
    }

    public function testDeleteAccountMissingPassword(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $this->client->request(
            'DELETE',
            '/api/profile',
            [], [],
            $this->authHeaders($token),
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testDeleteAccountRequiresAuth(): void
    {
        $this->client->request(
            'DELETE',
            '/api/profile',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['password' => 'password123'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteAccountCascadesReviewsAndCollections(): void
    {
        $user  = $this->createUser('user@test.com', 'regularuser');
        $token = $this->tokenFor($user);

        $film = new Film();
        $film->setTmdbId(rand(900000, 999999))->setTitle('Test Film');
        $this->em->persist($film);

        $uc = new UserCollection();
        $uc->setUser($user)->setFilm($film);
        $this->em->persist($uc);

        $review = new Review();
        $review->setUser($user)->setFilm($film)->setContent('Great film');
        $this->em->persist($review);

        $this->em->flush();

        $this->client->request(
            'DELETE',
            '/api/profile',
            [], [],
            $this->authHeaders($token),
            json_encode(['password' => 'password123'])
        );

        $this->assertResponseStatusCodeSame(204);

        $this->em->clear();
        $this->assertCount(0, $this->em->getRepository(UserCollection::class)->findBy(['user' => $user]));
        $this->assertCount(0, $this->em->getRepository(Review::class)->findBy(['user' => $user]));
    }
}
