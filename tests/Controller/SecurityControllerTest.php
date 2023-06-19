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
    // $this->assertTrue($this->client->getResponse()->isRedirect('/'));

    // Suivre la redirection et vérifier la page d'accueil
    $crawler = $this->client->followRedirect();
    $this->assertCount(1, $crawler->filter('h1'));
   

    }

    public function testLoginBadAuthentication(): void
    {
        // Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');

        // Remplir le formulaire de connexion avec les informations d'identification invalides
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'unknown_user';
        $form['_password'] = 'badPassword';
        $this->client->submit($form);

        // Vérifier que la redirection a eu lieu
        $this->assertTrue($this->client->getResponse()->isRedirection());

        // Suivre la redirection et obtenir la nouvelle page
        $crawler = $this->client->followRedirect();

        // Vérifier que la route actuelle est la route de la page de connexion
        $this->assertRouteSame('app_login');
   
        // Vérifier la présence du message d'erreur sur la page de connexion et du bouton de connexion
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertSelectorTextContains('.alert.alert-danger', 'Identifiants invalides.');
        $this->assertSelectorExists('button[type="submit"]');

    }

    public function testLogOut(): void
    {
        // Connectez-vous en tant qu'utilisateur
        $this->testLoginUser();

        // Effectuez une requête GET vers la route de déconnexion
        $this->client->request('GET', '/logout');

        // Vérifiez que la déconnexion a été effectuée avec succès
        $this->assertTrue($this->client->getResponse()->isRedirection());

        // Suivez la redirection vers la page de connexion
        $crawler = $this->client->followRedirect();

        // Vérifiez que la page de connexion est affichée
        $this->assertRouteSame('app_login');
        $this->assertSelectorExists('form[action="/login"]');
    }
}