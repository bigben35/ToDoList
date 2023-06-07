<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }


    public function load(ObjectManager $manager)
{
    // Création des utilisateurs
    $adminUser = new User();
    $adminUser->setUsername('admin');
    $adminUser->setEmail('admin@example.com');
    $adminUser->setRoles(['ROLE_ADMIN']);
    $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));

    $manager->persist($adminUser);


    for ($i = 1; $i <= 5; $i++) {
        $user = new User();
        $user->setUsername('user' . $i);
        $user->setEmail('user' . $i . '@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);
    }

    // Création des tâches pour l'administrateur (adminUser)
for ($i = 1; $i <= 5; $i++) {
    $task = new Task();
    $task->setTitle('Tâche Admin ' . $i);
    $task->setContent('Contenu de la tâche Admin ' . $i);
    $task->setCreatedAt(new \DateTime());
    $task->toggle(mt_rand(0, 1));
    $task->setUser($adminUser);
    $adminUser->addTask($task); // Ajoute la tâche à l'administrateur
    $manager->persist($task);
}

// Création des tâches pour les utilisateurs (ROLE_USER)
for ($j = 1; $j <= 10; $j++) {
    $task = new Task();
    $task->setTitle('Tâche User ' . $j);
    $task->setContent('Contenu de la tâche User ' . $j);
    $task->setCreatedAt(new \DateTime());
    $task->toggle(mt_rand(0, 1));
    $task->setUser($user);
    $user->addTask($task); // Ajoute la tâche à l'utilisateur
    $manager->persist($task);

}

    $manager->flush();
}
}