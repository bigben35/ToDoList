<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Tests\Controller\SecurityControllerTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    // public function setUp(): void
    // {
    //     $this->client = static::createClient();
    // }
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;

     protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        // Récupérer le TaskRepository à partir de l'EntityManager
        $this->taskRepository = $this->entityManager->getRepository(Task::class);
    }

    public function testListWhenNotAuthenticate(): void
    {
        $this->client->request('GET', '/tasks');

        // vérifions que la réponse renvoie un code de statut 302 (redirection) et que la redirection se fait vers la page de connexion (/login).
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // vérifie que la réponse de la requête est une redirection HTTP valide.
        $this->assertResponseRedirects();

        // suit la redirection et envoie une nouvelle requête vers la page de connexion.
        $this->client->followRedirect();

        // vérifie que la route de la page actuelle correspond à la route de connexion définie dans fichier de configuration.
        $this->assertRouteSame('app_login');
        // Si toutes les assertions passent sans erreur, cela signifie que l'utilisateur non authentifié est redirigé vers la page de connexion lorsqu'il tente d'accéder à la liste des tâches.
    }

    public function testListWhenAuthenticateAsUser(): void
    {
        // Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');

        // Remplir le formulaire de connexion avec les informations d'identification valides
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'user1';
        $form['_password'] = 'password';
        $this->client->submit($form);

        // Effectuer une requête GET vers la route de la liste des tâches
        $this->client->request('GET', '/tasks');

        // Vérifier que la réponse renvoie un code de statut HTTP 200
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // Vérifier que la route actuelle correspond à la route de la liste des tâches
        $this->assertSame('task_list', $this->client->getRequest()->attributes->get('_route'));
    
     }

     

    public function testCreateTaskLinkedToUser(): void
    {
        // Récupérer le référentiel de l'utilisateur
        $userRepository = $this->entityManager->getRepository(User::class);

        // Récupérer un utilisateur existant à partir de la base de données
        $user = $userRepository->findOneBy(['username' => 'user1']);

        // Créer une nouvelle tâche
        $task = new Task();
        $task->setTitle('Nouvelle tâche');
        $task->setContent('Description de la tâche');

        // Associer la tâche à l'utilisateur
        $task->setUser($user);

        // Enregistrer la tâche en utilisant le TaskRepository
        $this->taskRepository->save($task, true);

        // Récupérer la tâche enregistrée depuis la base de données
        $persistedTask = $this->taskRepository->find($task->getId());
        dd($persistedTask);

        // Vérifier si la tâche récupérée correspond à la tâche que vous avez créée
        $this->assertEquals($task->getTitle(), $persistedTask->getTitle());
        $this->assertEquals($task->getContent(), $persistedTask->getContent());

        // Vérifier si la tâche a été enregistrée avec succès
        $this->assertNotNull($task->getId());
    }
 }
