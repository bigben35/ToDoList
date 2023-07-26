<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private $userRepository;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }
    public function testRemoveUser(): void
{
    $user = $this->userRepository->findOneBy(['username' => 'newuser9']);
    $this->assertInstanceOf(User::class, $user);

    // Supprimer les tâches associées à l'utilisateur
    foreach ($user->getTasks() as $task) {
        $this->entityManager->remove($task);
    }

    $this->userRepository->remove($user, true);
    $this->entityManager->flush();
    $this->entityManager->clear();

    $this->assertNull($this->userRepository->findOneBy(['username' => 'newuser9']));
}


    public function testAddTask(): void
    {
        // Créer un nouvel utilisateur
        $user = new User();
        $user->setUsername('testuser32');
        $user->setEmail('testuser32@example.com');
        $user->setPassword('testpassword');

        // Créer une nouvelle tâche
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('This is a test task');

        // Ajouter la tâche à l'utilisateur
        $this->entityManager->persist($task); // Ajout de cette ligne
        $user->addTask($task);

        // Sauvegarder l'utilisateur
        $this->userRepository->save($user, true);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundUser = $this->userRepository->find($user->getId());
        $foundTask = $foundUser->getTasks()->first();

        $this->assertSame(1, $foundUser->getTasks()->count());
        $this->assertSame($task->getTitle(), $foundTask->getTitle());
        $this->assertSame($task->getContent(), $foundTask->getContent());
    }

//     public function testRemoveTask(): void
// {
//     // Créer un nouvel utilisateur
//     $user = new User();
//     $user->setUsername('testuser6');
//     $user->setEmail('testuser6@example.com');
//     $user->setPassword('testpassword');

//     // Créer une nouvelle tâche
//     $task = new Task();
//     $task->setTitle('Test Task');
//     $task->setContent('This is a test task');

//     // Ajouter la tâche à l'utilisateur
//     $user->addTask($task);

//     $this->entityManager->persist($user);
//     $this->entityManager->flush();
//     $this->entityManager->clear();

//     // Supprimer la tâche de l'utilisateur
//     $user->removeTask($task);

//     $this->entityManager->flush();
//     $this->entityManager->clear();

//     $foundUser = $this->userRepository->find($user->getId());

//     $this->assertSame(0, $foundUser->getTasks()->count());
// }


public function testUpgradePassword(): void
{
    // Créer un nouvel utilisateur
    $user = new User();
    $user->setUsername('testuser33');
    $user->setEmail('testuser33@example.com');
    
    // Hacher le nouveau mot de passe
    $newPassword = 'newpassword';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe de l'utilisateur
    $this->userRepository->upgradePassword($user, $hashedPassword);

    // Récupérer l'utilisateur mis à jour depuis la base de données
    $savedUser = $this->userRepository->find($user->getId());

    // Vérifier que le mot de passe a été mis à jour correctement
    $this->assertTrue(password_verify($newPassword, $savedUser->getPassword()));
}

    
}
