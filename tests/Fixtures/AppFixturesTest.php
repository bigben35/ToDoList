<?php 

use App\Entity\Task;
use App\Entity\User;
use App\DataFixtures\AppFixtures;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppFixturesTest extends WebTestCase
{
    protected static $client;

    public function setUp(): void
    {
        parent::setUp();

        if (!self::$client) {
            self::$client = static::createClient();
        }
    }


    public function testFixturesLoadSuccessfully()
{
    $client = self::$client;
    $container = $client->getContainer();
    $entityManager = $container->get('doctrine.orm.entity_manager');
    $userRepository = $entityManager->getRepository(User::class);
    $taskRepository = $entityManager->getRepository(Task::class);

    // Vérification des utilisateurs
    $users = $userRepository->findAll();
    $this->assertNotEmpty($users); // Vérifie que la liste des utilisateurs n'est pas vide

    // Vérification de l'administrateur
    $adminUser = $userRepository->findOneBy(['username' => 'admin']);
    $this->assertInstanceOf(User::class, $adminUser); // Vérifie que l'administrateur est une instance de la classe User
    $this->assertEquals('admin@example.com', $adminUser->getEmail()); // Vérifie l'e-mail de l'administrateur

    // Vérification des tâches de l'administrateur
    $adminTasks = $taskRepository->findBy(['user' => $adminUser]);
    $this->assertNotEmpty($adminTasks); // Vérifie que la liste des tâches de l'administrateur n'est pas vide
    foreach ($adminTasks as $task) {
        $this->assertInstanceOf(Task::class, $task); // Vérifie que chaque tâche est une instance de la classe Task
        $this->assertSame($adminUser, $task->getUser()); // Vérifie que l'utilisateur de chaque tâche est l'administrateur
    }

    // Vérification des tâches des utilisateurs
    $user = $userRepository->findOneBy(['username' => 'user5']);
    $this->assertInstanceOf(User::class, $user); // Vérifie que l'utilisateur est une instance de la classe User
    $this->assertEquals('user5@example.com', $user->getEmail()); // Vérifie l'e-mail de l'utilisateur

    $userTasks = $taskRepository->findBy(['user' => $user]);
    $this->assertNotEmpty($userTasks); // Vérifie que la liste des tâches de l'utilisateur n'est pas vide
    foreach ($userTasks as $task) {
        $this->assertInstanceOf(Task::class, $task); // Vérifie que chaque tâche est une instance de la classe Task
        $this->assertSame($user, $task->getUser()); // Vérifie que l'utilisateur de chaque tâche est l'utilisateur concerné
    }
}
}
