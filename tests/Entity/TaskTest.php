<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTitleTask()
    {
        $task = new Task();
        $task->setTitle('title');
        $this->assertSame('title', $task->getTitle());
    }

    public function testContentTask()
    {
        $task = new Task();
        $task->setContent('content');
        $this->assertSame('content', $task->getContent());
    }

    public function testAuthorTask()
{
    $user = new User();

    $task = new Task();

    // j'associe la tâche à un user avec setUser()
    $task->setUser($user);

    // vérifie si l'utilisateur associé à la tâche est correct
    $this->assertSame($user, $task->getUser());
}
}