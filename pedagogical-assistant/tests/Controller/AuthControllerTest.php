<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testRegisterUserSuccessfully(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'name' => 'Test User'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['message' => 'Utilisateur créé avec succès']);
    }

    public function testRegisterWithMissingFields(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'test@test.com'])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginSuccessfully(): void
    {
        $client = static::createClient();
        
        // Register first
        $client->request('POST', '/api/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'logintest@test.com',
                'password' => 'pass123',
                'name' => 'Login User'
            ])
        );

        // Then login
        $client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'logintest@test.com',
                'password' => 'pass123'
            ])
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['message' => 'Connexion réussie']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'nonexistent@test.com',
                'password' => 'wrongpass'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
