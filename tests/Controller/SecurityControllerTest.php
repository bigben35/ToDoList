<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLoginUser(): void
    {
        // Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');

        // Remplir le formulaire de connexion avec les informations d'identification valides
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'user1';
        $form['_password'] = 'password';
        $this->client->submit($form);

    //     // Vérifier la redirection vers la page d'accueil
    $this->assertTrue($this->client->getResponse()->isRedirect('/'));

    // Suivre la redirection et vérifier la page d'accueil
    $crawler = $this->client->followRedirect();
    $this->assertCount(1, $crawler->filter('h1'));
   

    }
}