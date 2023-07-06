<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\SecurityControllerTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;

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
        $form['_username'] = 'user5';
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
        $user = $userRepository->findOneBy(['username' => 'user5']);

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
        // dd($persistedTask);

        // Vérifier si la tâche récupérée correspond à la tâche que vous avez créée
        $this->assertEquals($task->getTitle(), $persistedTask->getTitle());
        $this->assertEquals($task->getContent(), $persistedTask->getContent());

        // Vérifier si la tâche a été enregistrée avec succès
        $this->assertNotNull($task->getId());
    }



    public function testEditTaskByAnUser(): void
    {
        // indique au client de suivre les redirections automatiquement, ce qui est utile lorsque vous effectuez une action qui déclenche une redirection (edit).
        $this->client->followRedirects();

        // récupère le UserRepository à partir du conteneur de dépendances de Symfony.
        $userRepository = static::getContainer()->get(UserRepository::class);

        // récupère le TaskRepository à partir du conteneur de dépendances de Symfony.
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // récupère l'utilisateur de test en utilisant le UserRepository et en le cherchant par son nom d'utilisateur.
        $testUser = $userRepository->findOneBy(['username' => 'user5']);

        // simule la connexion de l'utilisateur de test en utilisant le client Symfony.
        $this->client->loginUser($testUser);

        $idTask = $testUser->getTasks()->first()->getId();
        // dd($idtask);
        // envoie une requête GET pour accéder à la page d'édition de la tâche spécifique, en utilisant l'identifiant de la tâche
        $crawler = $this->client->request('GET', "/tasks/$idTask/edit");

        // vérifie que la réponse de la requête est réussie
        $this->assertResponseIsSuccessful();

        // sélectionne le formulaire d'édition de la tâche à partir du crawler (qui représente la page HTML récupérée) en utilisant le bouton "Modifier", avec titre et contenu modifiés
        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Tâche user5 edit',
            'task[content]' => 'Contenu de la tâche user5 edit',
        ]);
        $this->client->submit($form);

        // vérifie que la réponse de la requête a un code de statut HTTP 200 (OK).
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // vérifie que dans la réponse HTML, il y a un élément <div> contenant le texte "La tâche a bien été modifiée.
        $this->assertSelectorExists('div', 'La tâche a bien été modifiée.');

        $ModifiedTask = $taskRepository->findOneBy(['id' => $idTask]);

        // vérifie que le titre de la tâche modifiée contient la chaîne... 
        $this->assertStringContainsString('Tâche user5 edit', $ModifiedTask->getTitle());

        // vérifie que le contenu de la tâche modifiée contient la chaîne... 
        $this->assertStringContainsString('Contenu de la tâche user5 edit', $ModifiedTask->getContent());
    }


    public function testToggleTaskWithSuccess(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        
        $user = $userRepository->findOneBy(['username' => 'user5']);
        
        $this->client->loginUser($user);
        $this->client->followRedirects();

        $idTask = $user->getTasks()->first()->getId();
        $crawler = $this->client->request('GET', "/tasks/$idTask/toggle");

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('div.alert.alert-success');
    }

    public function testToggleTaskWithNotAGoodUser(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        
        $admin = $userRepository->findOneBy(['username' => 'admin']);
        $user = $userRepository->findOneBy(['username' => 'user5']);

        $task = $admin->getTasks()->first();
        
        $this->client->loginUser($user);
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/tasks/'.$task->getId().'/toggle');
        // $this->assertResponseStatus(403);
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertRouteSame('task_list');
        $this->assertSelectorExists('div.alert.alert-danger');
    }


    public function testAdminDeleteHisTask(): void
    {
        // Cette ligne indique au client de suivre les redirections automatiquement, ce qui est utile lorsque vous effectuez une action qui déclenche une redirection comme la suppression.
        $this->client->followRedirects();

        // récupère le UserRepository à partir du conteneur de dépendances de Symfony.
        $userRepository = static::getContainer()->get(UserRepository::class);

        // récupère l'admin
        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $task = $testUserAdmin->getTasks()->first();

        $this->client->loginUser($testUserAdmin);

        // envoie une requête GET pour accéder à la page de suppression de la tâche spécifique, en utilisant l'identifiant de la tâche.
        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        // réponse de la requête est réussie, c'est-à-dire qu'elle a un code de statut HTTP 2xx.
        $this->assertResponseIsSuccessful();

        // réponse HTML, il y a un élément <div> avec la classe alert-success contenant le texte "La tâche a bien été supprimée.". Cela permet de vérifier visuellement que la suppression de la tâche a été confirmée.
        $this->assertSelectorTextContains('html div.alert-success', "La tâche a bien été supprimée.");
    }

    public function testUserDeleteHisTask(): void
    {
        // Cette ligne indique au client de suivre les redirections automatiquement, ce qui est utile lorsque vous effectuez une action qui déclenche une redirection comme la suppression.
        $this->client->followRedirects();

        // récupère le UserRepository à partir du conteneur de dépendances de Symfony.
        $userRepository = static::getContainer()->get(UserRepository::class);

        // récupère l'user
        $testUser = $userRepository->findOneBy(['username' => 'user5']);

        $task = $testUser->getTasks()->first();

        // simulate $testUser being logged in
        $this->client->loginUser($testUser);

        // envoie une requête GET pour accéder à la page de suppression de la tâche spécifique, en utilisant l'identifiant de la tâche.
        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        // réponse de la requête est réussie, c'est-à-dire qu'elle a un code de statut HTTP 2xx.
        $this->assertResponseIsSuccessful();
        
        // réponse HTML, il y a un élément <div> avec la classe alert-success contenant le texte "La tâche a bien été supprimée.". Cela permet de vérifier visuellement que la suppression de la tâche a été confirmée.
        $this->assertSelectorTextContains('html div.alert-success', "La tâche a bien été supprimée.");
    }

    public function testUserRoleCannotDeleteAdminTask(): void
{
    // Cette ligne indique au client de suivre les redirections automatiquement, ce qui est utile lorsque vous effectuez une action qui déclenche une redirection comme la suppression.
    $this->client->followRedirects();

    // récupère le UserRepository à partir du conteneur de dépendances de Symfony.
    $userRepository = static::getContainer()->get(UserRepository::class);

    // récupère l'user avec le rôle ROLE_USER
    $testUser = $userRepository->findOneBy(['username' => 'user5']);

    // récupère l'admin
    $testUserAdmin = $userRepository->findOneBy(['username' => 'admin']);

    // récupère la première tâche créée par un utilisateur avec le rôle ROLE_ADMIN
    $task = $testUserAdmin->getTasks()->first();

    // simulate $testUser being logged in
    $this->client->loginUser($testUser);

    // envoie une requête GET pour accéder à la page de suppression de la tâche spécifique, en utilisant l'identifiant de la tâche.
    $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

    // vérifie que la réponse est une redirection vers une page d'erreur ou une page de refus d'accès
    // $this->assertResponseRedirects('/access-denied');

    // Vérifie que la réponse a un code de statut HTTP 403 (accès refusé)
    $this->assertSame(403, $this->client->getResponse()->getStatusCode());

    // vérifie que le message "Vous n'avez pas l'autorisation de supprimer cette tâche !" est affiché dans le flash bag
    $this->assertStringContainsString('Vous n\'avez pas l\'autorisation de supprimer cette tâche !', $this->client->getResponse()->getContent());

}
 }
