<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private UserPasswordHasher $userPasswordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userPasswordHasher = $this->client->getContainer()->get('security.user_password_hasher');
        $this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
    }

    private function createUser(): User
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

    private function createAdmin(): User
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

    public function testUsersList(): void
    {
        $this->client->request('GET', '/users');
        $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $this->assertEquals('/login', $currentUrl);

        $this->client->loginUser($this->createUser());
        $this->client->request('GET', '/users');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $this->client->loginUser($this->createAdmin());
        $crawler = $this->client->request('GET', '/users');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
        $this->assertCount(1, $crawler->filter('table'));
    }

    public function testUserCreationSuccess(): void
    {
        $this->client->request('GET', '/users/create');
        $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $this->assertEquals('/login', $currentUrl);

        $this->client->loginUser($this->createUser());
        $this->client->request('GET', '/users/create');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $this->client->loginUser($this->createAdmin());
        $this->client->request('GET', '/users/create');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');

        $this->client->submitForm('Ajouter', ['user[username]' => 'createduser', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();
        $user = $this->userRepository->findOneByEmail('createduser@email.fr');
        $userCount = $this->userRepository->findByEmail('createduser@email.fr');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $userCount);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
        $this->assertEquals('/users', $currentUrl);
        $this->assertEquals('L\'utilisateur a bien été ajouté.', trim($crawler->filter('.alert-success')->text()));
    }

    public function testUserCreationFailureOnUsernameUnicity(): void
    {
        $this->client->loginUser($this->createAdmin());

        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', ['user[username]' => 'createduser', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'user1@email.com', 'user[roles]' => 'ROLE_USER']);
        $this->client->followRedirect();

        $this->client->request('GET', '/users/create');
        $crawler = $this->client->submitForm('Ajouter', ['user[username]' => 'createduser', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'user2@email.com', 'user[roles]' => 'ROLE_USER']);

        $currentUrl = $this->client->getRequest()->getPathInfo();
        $userCount = $this->userRepository->findByUsername('createduser');
        $userCountByMail = $this->userRepository->findByEmail('user2@email.com');

        $this->assertEquals('/users/create', $currentUrl);
        $this->assertCount(1, $userCount);
        $this->assertCount(0, $userCountByMail);
        $this->assertEquals('Ce nom d\'utilisateur est déjà utilisé.', trim($crawler->filter('.form-error-message')->text()));
    }

    public function testUserCreationFailureOnEmailUnicity(): void
    {
        $this->client->loginUser($this->createAdmin());

        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', ['user[username]' => 'createduserjohn', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);
        $this->client->followRedirect();

        $this->client->request('GET', '/users/create');
        $crawler = $this->client->submitForm('Ajouter', ['user[username]' => 'createduseremily', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);

        $currentUrl = $this->client->getRequest()->getPathInfo();
        $userCount = $this->userRepository->findByEmail('createduser@email.fr');
        $userCountByUsername = $this->userRepository->findByUsername('createduseremily');

        $this->assertEquals('/users/create', $currentUrl);
        $this->assertCount(1, $userCount);
        $this->assertCount(0, $userCountByUsername);
        $this->assertEquals('L\'email est déjà utilisé par un autre utilisateur.', trim($crawler->filter('.form-error-message')->text()));
    }

    public function testUserCreationFailureOnPasswordMistake(): void
    {
        $this->client->loginUser($this->createAdmin());

        $this->client->request('GET', '/users/create');
        $crawler = $this->client->submitForm('Ajouter', ['user[username]' => 'Babylone', 'user[password][first]' => 'password', 'user[password][second]' => 'password2', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);

        $currentUrl = $this->client->getRequest()->getPathInfo();
        $userCount = $this->userRepository->findByEmail('createduser@email.fr');

        $this->assertEquals('/users/create', $currentUrl);
        $this->assertCount(0, $userCount);
        $this->assertEquals('Les deux mots de passe doivent correspondre.', trim($crawler->filter('.form-error-message')->text()));
    }

    public function testUserEdit(): void
    {
        $this->client->loginUser($this->createAdmin());
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', ['user[username]' => 'createduser', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);
        $this->client->followRedirect();

        $user = $this->userRepository->findOneByUsername('createduser');
        $userId = $user->getId();
        $this->client->request('GET', '/users/'.$userId.'/edit');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Modifier '.$user->getUsername());

        $this->client->submitForm('Modifier', ['user[username]' => 'createduserEdited', 'user[password][first]' => 'password', 'user[password][second]' => 'password', 'user[email]' => 'createduser@email.fr', 'user[roles]' => 'ROLE_USER']);
        $crawler = $this->client->followRedirect();

        $currentUrl = $this->client->getRequest()->getPathInfo();
        $userCount = $this->userRepository->findByUsername('createduserEdited');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $userCount);
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
        $this->assertEquals('/users', $currentUrl);
        $this->assertEquals('L\'utilisateur a bien été modifié.', trim($crawler->filter('.alert-success')->text()));
    }
}
