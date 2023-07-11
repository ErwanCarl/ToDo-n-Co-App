<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEntity(): void
    {
        $task1 = new Task();
        $task2 = new Task();
        $user = new User();
        $user
            ->setUsername('John Doe')
            ->setEmail('john.doe@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_ADMIN'])
            ->addTask($task1)
            ->addTask($task2)
            ->removeTask($task1)
        ;

        $this->assertEquals('John Doe', $user->getUsername());
        $this->assertEquals('john.doe@gmail.com', $user->getEmail());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals(['ROLE_ADMIN','ROLE_USER'], $user->getRoles());
        $this->assertCount(1, $user->getTasks());
        $this->assertContains($task2, $user->getTasks());
        $this->assertNotContains($task1, $user->getTasks());
    }
}
