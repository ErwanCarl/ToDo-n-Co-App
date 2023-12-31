<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasher = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $users = [];
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        for ($i = 0; $i < 10; ++$i) {
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
            ->setUsername('Super Admin')
            ->setEmail('super.admin@orange.fr')
            ->setPassword($this->userPasswordHasher->hashPassword($superAdminUser, 'password'))
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($superAdminUser);

        $anonymousTasks = [];
        for ($i = 0; $i < 10; ++$i) {
            $anonymousTasks[$i] = new Task();
            $anonymousTasks[$i]->setTitle($faker->unique()->sentence(3));
            $anonymousTasks[$i]->setContent($faker->unique()->paragraph(2));
            $randomTimestamp = mt_rand(strtotime('2023-01-01'), strtotime('2023-08-31'));
            $randomDateTime = new \DateTimeImmutable('@'.$randomTimestamp);
            $anonymousTasks[$i]->setCreatedAt($randomDateTime);
            $anonymousTasks[$i]->setIsDone(rand(0, 1));

            $manager->persist($anonymousTasks[$i]);
        }

        $tasks = [];
        for ($i = 0; $i < 50; ++$i) {
            $tasks[$i] = new Task();
            $tasks[$i]->setTitle($faker->unique()->sentence(3));
            $tasks[$i]->setContent($faker->unique()->paragraph(2));
            $randomTimestamp = mt_rand(strtotime('2023-01-01'), strtotime('2023-08-31'));
            $randomDateTime = new \DateTimeImmutable('@'.$randomTimestamp);
            $tasks[$i]->setCreatedAt($randomDateTime);
            $tasks[$i]->setIsDone(rand(0, 1));
            $randomUser = array_rand($users, 1);
            $tasks[$i]->setUser($users[$randomUser]);

            $manager->persist($tasks[$i]);
        }

        $manager->flush();
    }
}
