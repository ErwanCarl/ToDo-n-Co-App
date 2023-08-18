<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

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

    public function testLoginAccess(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(1, $crawler->filter('form.login-form')->count());
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginSuccess(): void
    {
        $this->createUser();

        $this->client->request(Request::METHOD_GET, '/login');
        $this->client->submitForm('Confirmer', ['email' => 'user@email.fr', 'password' => 'password']);
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('Vous êtes désormais connecté.', trim($crawler->filter('.alert-success')->text()));
        $this->assertEquals('/', $currentUrl); // Redirect after successful login
    }

    public function testLoginFailure(): void
    {
        $this->client->request(Request::METHOD_GET, '/login');
        $this->client->submitForm('Confirmer', ['email' => 'invalid@email.fr', 'password' => 'invalidpassword']);
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/login', $currentUrl);

        // Ensure the correct error message is displayed
        $this->assertCount(1, $crawler->filter('.alert-danger'));
        $this->assertEquals('Identifiants invalides.', trim($crawler->filter('.alert-danger')->text()));
    }

    public function testAuthenticatedUserRedirectsFromLoginPage(): void
    {
        $this->client->loginUser($this->createUser());

        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/');

        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        // Ensure redirection to homepage if user is already authenticated
        $this->assertEquals('/', $currentUrl);
        $this->assertCount(1, $crawler->filter('.alert-danger'));
        $this->assertEquals('Vous êtes déjà connecté.', trim($crawler->filter('.alert-danger')->text()));
    }

    public function testLogout(): void
    {
        $this->client->loginUser($this->createUser());
        $crawler = $this->client->request('GET', '/logout');
        $crawler = $this->client->followRedirect();
        $currentUrl = $this->client->getRequest()->getPathInfo();

        $this->assertEquals('/login', $currentUrl);
        $this->assertCount(1, $crawler->filter('.alert-success'));
        $this->assertEquals('Vous êtes désormais déconnecté.', trim($crawler->filter('.alert-success')->text()));
    }
}
