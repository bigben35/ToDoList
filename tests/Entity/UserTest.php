<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testCreateUserIsTrue()
    {
        $user = new User();

        $user->setUsername('username')
            ->setEmail('test@test.com')
            ->setPassword('password')
            ->setRoles(['ROLE_ADMIN']);

        $this->assertTrue($user->getUsername() === 'username');        
        $this->assertTrue($user->getEmail() === 'test@test.com');        
        $this->assertTrue($user->getPassword() === 'password');        
        $this->assertContains('ROLE_ADMIN', $user->getRoles());       
    }
}