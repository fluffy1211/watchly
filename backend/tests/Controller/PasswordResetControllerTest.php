<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\PasswordResetService;
use App\Tests\BaseWebTestCase;

class PasswordResetControllerTest extends BaseWebTestCase
{
    private function register(string $email, string $password, string $username): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password, 'username' => $username, 'consent' => true])
        );
    }

    private function requestReset(string $email): void
    {
        $this->client->request(
            'POST',
            '/api/password-reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email])
        );
    }

    private function reset(string $token, string $password, string $confirmation): void
    {
        $this->client->request(
            'POST',
            '/api/password-reset/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['token' => $token, 'password' => $password, 'password_confirmation' => $confirmation])
        );
    }

    private function service(): PasswordResetService
    {
        return static::getContainer()->get(PasswordResetService::class);
    }

    public function testRequestUnknownEmailIsAccepted(): void
    {
        $this->requestReset('nobody@example.com');

        $this->assertResponseStatusCodeSame(202);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);

        $this->em->clear();
        $this->assertNull($this->em->getRepository(User::class)->findOneBy(['email' => 'nobody@example.com']));
    }

    public function testRequestKnownEmailStoresToken(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $this->requestReset('test@example.com');

        $this->assertResponseStatusCodeSame(202);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user->getResetToken());
        $this->assertNotNull($user->getResetTokenExpiresAt());
        $this->assertGreaterThan(new \DateTimeImmutable(), $user->getResetTokenExpiresAt());
    }

    public function testResetWithValidToken(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $token = $this->service()->request('test@example.com');
        $this->assertNotNull($token);

        $this->reset($token, 'newpassword456', 'newpassword456');

        $this->assertResponseStatusCodeSame(200);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNull($user->getResetToken());
        $this->assertNull($user->getResetTokenExpiresAt());

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'test@example.com', 'password' => 'newpassword456'])
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testResetPasswordConfirmationMismatch(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $token = $this->service()->request('test@example.com');

        $this->reset($token, 'newpassword456', 'somethingelse');

        $this->assertResponseStatusCodeSame(422);
    }

    public function testResetShortPassword(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $token = $this->service()->request('test@example.com');

        $this->reset($token, 'short', 'short');

        $this->assertResponseStatusCodeSame(422);
    }

    public function testResetUnknownToken(): void
    {
        $this->reset(str_repeat('a', 64), 'newpassword456', 'newpassword456');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testResetExpiredToken(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $token = $this->service()->request('test@example.com');

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('-1 hour'));
        $this->em->flush();

        $this->reset($token, 'newpassword456', 'newpassword456');

        $this->assertResponseStatusCodeSame(400);
    }
}
