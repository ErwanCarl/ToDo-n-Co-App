<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasher = $userPasswordHasherInterface;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $users = [];
        $roles = array('ROLE_USER', 'ROLE_ADMIN');
        for ($i = 0; $i < 10; $i++) {
            $users[$i] = new User();
            $users[$i]->setUsername($faker->unique()->firstName.' '.$faker->unique()->lastName);
            $users[$i]->setEmail($faker->unique()->email);
            $users[$i]->setPassword($this->userPasswordHasher->hashPassword($users[$i], 'password'));
            $randomKey = array_rand($roles, 1);
            $users[$i]->setRoles([$roles[$randomKey]]);

            $manager->persist($users[$i]);
        }

        $superAdminUser = new User();
        $superAdminUser
            ->setUsername('Erwan Carlini')
            ->setEmail('erwan.carlini@orange.fr')
            ->setPassword($this->userPasswordHasher->hashPassword($superAdminUser, 'password'))
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($superAdminUser);

        $tasks = [];
        for ($i = 0; $i < 50; $i++) {
            $tasks[$i] = new Task();
            $tasks[$i]->setTitle($faker->unique()->sentence(3));
            $tasks[$i]->setContent($faker->unique()->paragraph(2));
            $randomTimestamp = mt_rand(strtotime('2023-01-01'), strtotime('2023-08-31'));
            $randomDateTime = new DateTimeImmutable('@' . $randomTimestamp);
            $tasks[$i]->setCreatedAt($randomDateTime);
            $tasks[$i]->setIsDone(rand(0,1));
            $randomUser = array_rand($users, 1);
            $tasks[$i]->setUser($users[$randomUser]);

            $manager->persist($tasks[$i]);
        }

        $manager->flush();
    }
}
