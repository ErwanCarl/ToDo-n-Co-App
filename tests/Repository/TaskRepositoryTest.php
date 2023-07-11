<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{     
    public function setUp() : void
    {
        self::bootKernel();
    }

    public function testCreatedQueryBuilder(): void
    {
        $taskRepository = new TaskRepository(static::getContainer()->get(ManagerRegistry::class));

        $tasks = $taskRepository->findUserTasks();
        $tasksDone = $taskRepository->findUserTasksDone();

        if($tasks) {
            foreach($tasks as $task) {
                $this->assertInstanceOf(Task::class, $task);
                $this->assertEquals(0, $task->isIsDone());
            }
        }
        
        if($tasksDone) {
            foreach($tasksDone as $taskDone) {
                $this->assertInstanceOf(Task::class, $taskDone);
                $this->assertEquals(1, $taskDone->isIsDone());            
            }
        }
    }

    public function testCreateAndRemoveTask() : void 
    {
        $taskRepository = new TaskRepository(static::getContainer()->get(ManagerRegistry::class));
        $userRepository = new UserRepository(static::getContainer()->get(ManagerRegistry::class));

        $task = new Task();
        $task
            ->setTitle('TestTitle')
            ->setContent('Content test')
            ->setUser($userRepository->findOneByUsername('Super Admin'))
        ;   
        $taskRepository->save($task, true);
        $this->assertNotNull($taskRepository->findOneByTitle('TestTitle'));

        $taskRepository->remove($task, true);
        $this->assertNull($taskRepository->findOneByTitle('TestTitle'));
    }
}
