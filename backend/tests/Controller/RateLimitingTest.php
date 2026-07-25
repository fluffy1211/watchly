<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class RateLimitingTest extends BaseWebTestCase
{
    private function register(string $email, string $password, string $username): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password, 'username' => $username])
        );
    }

    private function login(string $email, string $password): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password])
        );
    }

    public function testRegisterIsAllowedUnderTheLimit(): void
    {
        $this->register('a@example.com', 'password123', 'usera');
        $this->assertResponseStatusCodeSame(201);

        $this->register('b@example.com', 'password123', 'userb');
        $this->assertResponseStatusCodeSame(201);

        $this->register('c@example.com', 'password123', 'userc');
        $this->assertResponseStatusCodeSame(201);
    }

    public function testRegisterIsBlockedAfterLimitExceeded(): void
    {
        $this->register('a@example.com', 'password123', 'usera');
        $this->register('b@example.com', 'password123', 'userb');
        $this->register('c@example.com', 'password123', 'userc');

        $this->register('d@example.com', 'password123', 'userd');

        $this->assertResponseStatusCodeSame(429);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($this->client->getResponse()->headers->has('Retry-After'));
    }

    public function testLoginIsBlockedAfterTooManyFailedAttempts(): void
    {
        $this->register('throttle@example.com', 'password123', 'throttleuser');
        $this->assertResponseStatusCodeSame(201);

        for ($i = 0; $i < 5; ++$i) {
            $this->login('throttle@example.com', 'wrong-password');
        }

        $this->login('throttle@example.com', 'wrong-password');

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('Too many failed login attempts', $data['message']);
    }
}
