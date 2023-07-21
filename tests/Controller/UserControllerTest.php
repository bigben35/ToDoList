<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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


    public function testCreateUserByAdmin(): void
    {
        $this->client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    
        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin']);
    
        $this->client->loginUser($testUserAdmin);
        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
    
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'newuser10';
        $form['user[email]'] = 'newuser10@example.com';
        $form['user[password][first]'] = 'password';
        $form['user[password][second]'] = 'password';
        $form['user[roles]']->select('ROLE_USER');
    
        $this->client->submit($form);
        // $response = $this->client->getResponse();
        // $this->assertTrue($response->isRedirect('/users'));
        $this->assertResponseIsSuccessful();
        // $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été ajouté.');
    
        // Vérification des données de l'utilisateur créé
        $createdUser = $userRepository->findOneBy(['username' => 'newuser10']);
        $this->assertNotNull($createdUser);
        $this->assertSame('newuser10', $createdUser->getUsername());
        $this->assertSame('newuser10@example.com', $createdUser->getEmail());
        $this->assertTrue(in_array('ROLE_USER', $createdUser->getRoles()));
    }
    


    public function testEditUserByAdmin(): void
{
    $this->client->followRedirects();
    $userRepository = static::getContainer()->get(UserRepository::class);
    $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

    $testUserAdmin = $userRepository->findOneBy(['username' => 'admin']);
    $testUser = $userRepository->findOneBy(['username' => 'updateduser']);

    $this->client->loginUser($testUserAdmin);
    $crawler = $this->client->request('GET', '/users/'.$testUser->getId().'/edit');
    $this->assertResponseIsSuccessful();

    $form = $crawler->selectButton('Modifier')->form();
    $form['user[username]'] = 'updateduser1';
    $form['user[email]'] = 'updateduser1@example.com';
    $form['user[password][first]'] = 'password';
    $form['user[password][second]'] = 'password';
    $form['user[roles]']->select('ROLE_USER');

    $this->client->submit($form);
    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été modifié.');

    // Vérification des données de l'utilisateur modifié
    $updatedUser = $userRepository->findOneBy(['username' => 'updateduser1']);
    $this->assertNotNull($updatedUser);
    $this->assertSame('updateduser1', $updatedUser->getUsername());
    $this->assertSame('updateduser1@example.com', $updatedUser->getEmail());
    $this->assertTrue(in_array('ROLE_USER', $updatedUser->getRoles()));
}

}