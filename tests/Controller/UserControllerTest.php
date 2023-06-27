<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    
    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testListWhenNotLogIn(): void
    {
        $this->client->request('GET', '/users');

        $response = $this->client->getResponse();
        $this->assertSame(302, $response->getStatusCode());
        
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    public function testCreateUserPageUnauthorizedToRoleUser(): void
    {
        
        $this->client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['username' => 'user5']);

        $this->client->loginUser($testUser);
        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    public function testUsersPageAuthorizedForAdmin(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $this->client->loginUser($testUserAdmin);
        $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }
}