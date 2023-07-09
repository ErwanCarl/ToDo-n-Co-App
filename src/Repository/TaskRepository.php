<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUserTasks() : ?array 
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isDone = :isDone')
            ->setParameter('isDone', 0)
            ->orderBy('c.createdAt','ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserTasksDone() : ?array 
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isDone = :isDone')
            ->setParameter('isDone', 1)
            ->orderBy('c.createdAt','ASC')
            ->getQuery()
            ->getResult();
    }
}
