<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class AuthControllerTest extends BaseWebTestCase
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

    public function testRegisterSuccess(): void
    {
        $this->register('test@example.com', 'password123', 'testuser');

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Account created successfully', $data['message']);
        $this->assertSame('test@example.com', $data['user']['email']);
        $this->assertSame('testuser', $data['user']['username']);
        $this->assertArrayHasKey('id', $data['user']);
    }

    public function testRegisterDuplicateEmail(): void
    {
        $this->register('dup@example.com', 'password123', 'user1');
        $this->assertResponseStatusCodeSame(201);

        $this->register('dup@example.com', 'password123', 'user2');
        $this->assertResponseStatusCodeSame(409);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Email already in use', $data['message']);
    }

    public function testRegisterDuplicateUsername(): void
    {
        $this->register('first@example.com', 'password123', 'sameuser');
        $this->assertResponseStatusCodeSame(201);

        $this->register('second@example.com', 'password123', 'sameuser');
        $this->assertResponseStatusCodeSame(409);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Username already in use', $data['message']);
    }

    public function testRegisterInvalidEmail(): void
    {
        $this->register('not-an-email', 'password123', 'testuser');

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testRegisterShortPassword(): void
    {
        $this->register('test@example.com', 'short', 'testuser');

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testRegisterShortUsername(): void
    {
        $this->register('test@example.com', 'password123', 'ab');

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testLoginSuccess(): void
    {
        $this->register('login@example.com', 'password123', 'loginuser');
        $this->assertResponseStatusCodeSame(201);

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'login@example.com', 'password' => 'password123'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginWrongPassword(): void
    {
        $this->register('wrongpw@example.com', 'password123', 'wrongpwuser');

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'wrongpw@example.com', 'password' => 'wrongpassword'])
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
