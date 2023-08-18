<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $userRepository;
    private $taskRepository;
    private $userPasswordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userPasswordHasher = $this->client->getContainer()->get('security.user_password_hasher');
        $this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->taskRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
    }
    
    private function createUser()
    {
        $user = new User();
        $user
            ->setEmail('user@email.fr')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'password'))
            ->setUsername('username')
            ->setRoles(['ROLE_USER']);
        $this->userRepository->save($user, true);
        return $user;
    }
    
    public function testTasksList()
    {
        $this->client->request('GET', '/tasks');
        $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/login', $currentUrl);

        $this->client->loginUser($this->createUser());
        $crawler =  $this->client->request('GET', '/tasks');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', "Liste des tâches à réaliser");
        $this->assertSelectorTextContains('.btn-info', "Créer une nouvelle tâche");

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $crawler = $this->client->click($link);
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/tasks/create', $currentUrl);

        $link = $crawler->selectLink('Retour à la liste des tâches')->link();
        $crawler = $this->client->click($link);
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/tasks', $currentUrl);
    }

    public function testTasksDone()
    {
        $this->client->request('GET', '/tasks/done');
        $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/login', $currentUrl);

        $this->client->loginUser($this->createUser());
        $crawler = $this->client->request('GET', '/tasks/done');

        $this->assertSelectorTextContains('h1', "Liste des tâches terminées");
        $this->assertSelectorTextContains('.btn-info', "Créer une nouvelle tâche");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $crawler = $this->client->click($link);
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/tasks/create', $currentUrl);

        $link = $crawler->selectLink('Retour à la liste des tâches')->link();
        $crawler = $this->client->click($link);
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/tasks', $currentUrl);
    }

    public function testTasksCreate()
    {
        $this->client->request('GET', '/tasks/create');
        $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $this->assertEquals('/login', $currentUrl);

        $user = $this->createUser();
        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/create');

        $this->assertSelectorTextContains('h1', "Création de tâche");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('Ajouter', ['task[title]'=>'Title', 'task[content]'=>'Content']);
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $task = $this->taskRepository->findOneByUser($user);
        $userTask = $task->getUser();
        $taskCount = $this->taskRepository->findByTitle('Title');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame($user, $userTask);
        $this->assertCount(1, $taskCount);
        $this->assertEquals('/tasks', $currentUrl);
        $this->assertResponseIsSuccessful();
        $this->assertEquals('La tâche a été bien été ajoutée.', trim($crawler->filter('.alert-success')->text()));
    }

    public function testTasksEdit()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', ['task[title]'=>'Title', 'task[content]'=>'Content']);
        $this->client->followRedirect();
        $task = $this->taskRepository->findOneByUser($user);

        $taskId = $task->getId();
        $this->client->request('GET', '/tasks/'.$taskId.'/edit');
        $this->assertSelectorTextContains('h1', "Modification de tâche");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('Modifier', ['task[title]'=>'EditedTitle', 'task[content]'=>'EditedContent']);
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $task = $this->taskRepository->findOneByUser($user);
        $taskCount = $this->taskRepository->findByTitle('Title');
        $editedTaskCount = $this->taskRepository->findByTitle('EditedTitle');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $taskCount);
        $this->assertCount(1, $editedTaskCount);
        $this->assertEquals('/tasks', $currentUrl);
        $this->assertEquals('La tâche a bien été modifiée.', trim($crawler->filter('.alert-success')->text()));

        $link = $crawler->selectLink('EditedTitle')->link();
        $crawler = $this->client->click($link);
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/tasks/'.$taskId.'/edit', $currentUrl);
    }

    public function testTasksToggle()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', ['task[title]'=>'Title', 'task[content]'=>'Content']);
        $crawler = $this->client->followRedirect();
        $task = $this->taskRepository->findOneByUser($user);
        $taskId = $task->getId();

        // $this->client->request('GET', '/tasks/'.$taskId.'/toggle');

        // Find the specific <h4> by its content
        $h4ToClick = $crawler->filter('h4:contains("Title")')->first();
        // Find the parent .thumbnail div containing the <h4> and the toggle button
        $thumbnailDiv = $h4ToClick->closest('.thumbnail');
        // Use the button text to find the specific toggle button in the parent
        $toggleButton = $thumbnailDiv->selectButton('Marquer comme faite');
        // Submit the form associated with the toggle button
        $this->client->submit($toggleButton->form());;

        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $task = $this->taskRepository->findOneByTitle('Title');
        // dd($task->isIsDone());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertEquals('/tasks', $currentUrl);
        // $this->assertEquals(true, $task->isIsDone());
        $this->assertEquals("La tâche '".$task->getTitle()."' a bien été marquée comme faite.", trim($crawler->filter('.alert-success')->text()));

        $crawler = $this->client->request('GET', '/tasks/done');
        $filteredH4 = $crawler->filter('h4:contains("Title")');
        $this->assertCount(1, $filteredH4);

        $this->client->request('GET', '/tasks/'.$taskId.'/toggle');
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $task = $this->taskRepository->findOneByTitle('Title');
        $filteredH4 = $crawler->filter('h4:contains("Title")');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertEquals('/tasks', $currentUrl);
        $this->assertEquals(false, $task->isIsDone());
        $this->assertEquals("La tâche '".$task->getTitle()."' a bien été replacé dans les tâches à faire.", trim($crawler->filter('.alert-success')->text()));
        $this->assertCount(1, $filteredH4);

        $this->client->request('GET', '/tasks/'.$taskId.'/delete');
    }

    public function testTasksDelete()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', ['task[title]'=>'Title', 'task[content]'=>'Content']);
        $crawler = $this->client->followRedirect();
        
        // Find the specific <h4> by its content
        $h4ToClick = $crawler->filter('h4:contains("Title")')->first();
        // Find the parent .thumbnail div containing the <h4> and the toggle button
        $thumbnailDiv = $h4ToClick->closest('.thumbnail');
        // Use the button text to find the specific toggle button in the parent
        $deleteButton = $thumbnailDiv->selectButton('Supprimer');
        // Submit the form associated with the toggle button
        $this->client->submit($deleteButton->form());;

        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $taskCount = $this->taskRepository->findByTitle('Title');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertEquals('/tasks', $currentUrl);
        $this->assertCount(0, $taskCount);
        $this->assertEquals("La tâche a bien été supprimée.", trim($crawler->filter('.alert-success')->text()));
    }
}
