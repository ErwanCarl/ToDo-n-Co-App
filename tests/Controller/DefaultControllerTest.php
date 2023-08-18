<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $client;
    private $userRepository;
    private $userPasswordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $this->userPasswordHasher = $this->client->getContainer()->get('security.user_password_hasher');
        $this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
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

    private function createAdmin()
    {
        $admin = new User();
        $admin
            ->setEmail('admin@email.fr')
            ->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'))
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN']);
        $this->userRepository->save($admin, true);

        return $admin;
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        $this->assertCount(0, $crawler->filter('.tasks-buttons a'));
        $this->assertCount(2, $crawler->filter('.navbar a'));
        $this->assertEquals('Connexion', trim($crawler->filter('.navbar #nav2 a')->text()));
        $this->assertEquals('Accueil', trim($crawler->filter('.navbar #nav1 a')->text()));
    }

    public function testAuthenticatedUserButtons()
    {
        $this->client->loginUser($this->createUser());

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('.navbar a'));
        $this->assertEquals('Déconnexion', trim($crawler->filter('.navbar #nav2 a')->text()));
        $this->assertEquals('Accueil', trim($crawler->filter('.navbar #nav1 a')->text()));
        $this->assertCount(3, $crawler->filter('.tasks-buttons a'));
        $this->assertEquals('Créer une nouvelle tâche', trim($crawler->filter('.tasks-buttons a.btn-success')->text()));
        $this->assertEquals('Consulter la liste des tâches à faire', trim($crawler->filter('.tasks-buttons a.btn-info')->text()));
        $this->assertEquals('Consulter la liste des tâches terminées', trim($crawler->filter('.tasks-buttons a.btn-secondary')->text()));
    }

    public function testAuthenticatedAdminButtons()
    {
        $this->client->loginUser($this->createAdmin());

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(4, $crawler->filter('.navbar a'));
        $this->assertEquals('Déconnexion', trim($crawler->filter('.navbar #nav2 a')->text()));
        $this->assertEquals('Accueil', trim($crawler->filter('#nav1 > ul > li:nth-child(1) > a')->text()));
        $this->assertEquals('Créer un utilisateur', trim($crawler->filter('#nav1 > ul > li:nth-child(2) > a')->text()));
        $this->assertEquals('Liste des utilisateurs', trim($crawler->filter('#nav1 > ul > li:nth-child(3) > a')->text()));

        $this->assertCount(3, $crawler->filter('.tasks-buttons a'));
        $this->assertEquals('Créer une nouvelle tâche', trim($crawler->filter('.tasks-buttons a.btn-success')->text()));
        $this->assertEquals('Consulter la liste des tâches à faire', trim($crawler->filter('.tasks-buttons a.btn-info')->text()));
        $this->assertEquals('Consulter la liste des tâches terminées', trim($crawler->filter('.tasks-buttons a.btn-secondary')->text()));
    }
}
