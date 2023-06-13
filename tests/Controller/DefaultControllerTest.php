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

        //vérifie que la réponse HTTP a un code de statut 200, ce qui signifie que la requête a réussi et que la page a été trouvée.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //vérifie que le nombre d'éléments <h1> dans la page est égal à 1. Cela garantit que le titre principal est présent sur la page.
        $this->assertCount(1, $crawler->filter('h1'));
    }
}
