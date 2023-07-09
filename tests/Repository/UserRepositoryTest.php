<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{    
    public function setUp() : void
    {
        self::bootKernel();
    }
    
    public function testCreateAndRemoveUser() : void 
    {
        $userRepository = new UserRepository(static::getContainer()->get(ManagerRegistry::class));
        $user = new User();

        $user 
            ->setEmail('user@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_ADMIN'])
            ->setUsername('User')
        ; 

        $userRepository->save($user, true);
        $this->assertNotNull($userRepository->findOneByEmail('user@gmail.com'));

        $userRepository->remove($user, true);
        $this->assertNull($userRepository->findOneByEmail('user@gmail.com'));
    }
}
