<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testHomePage()
    {
        //créer une instance  du client de test
        $client = static::createClient();

        //requête client
        $crawler = $client->request('GET', '/');

        // Si l'on est pas connecté, on est censé être redirigé vers la page de login
        // Vérifier que la réponse a un code de statut de 302, indiquant une redirection
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), "En attente d'un statut HTTP 302, et non " . $client->getResponse()->getStatusCode());

        // Suivre la redirection et vérifier que l'on est bien sur la page de login
        $crawler = $client->followRedirect();

        // Vérifier la présence d'un bouton "Se connecter"
        $buttonCrawler = $crawler->selectButton('Se connecter');
        $this->assertGreaterThan(0, $buttonCrawler->count(), "Aucun bouton 'Se connecter' trouvé");
    }
}
