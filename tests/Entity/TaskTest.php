<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskEntity(): void
    {
        $user = New User();
        $task = new Task();
        $task
            ->setTitle('TestTitle')
            ->setContent('Content test')
            ->setUser($user)
        ;   
            
        $this->assertEquals('Content test', $task->getContent());
        $this->assertEquals('TestTitle', $task->getTitle());
        $this->assertEquals($user, $task->getUser());
        $this->assertInstanceOf(DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertEquals(false, $task->isIsDone());

        $task->toggle(!$task->isIsDone());
        $this->assertEquals(true, $task->isIsDone());
    }
}
