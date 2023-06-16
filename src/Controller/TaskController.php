<?php

namespace App\Controller;

use App\Entity\Task;
use AppBundle\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list', methods: ['GET'])]
    public function listAction(TaskRepository $taskRepository) : Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findAll()]);
    }

    #[Route('/tasks/create', name: 'task_create', methods: ['GET', 'POST'])]
     public function createAction(Request $request, TaskRepository $taskRepository) : Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($user);
            $taskRepository->save($task, true);

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/create.html.twig', ['form' => $form]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
     public function editAction(#[MapEntity(id:'id')]Task $task, Request $request, TaskRepository $taskRepository) : Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTaskAction(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository) : Response
    {
        $task->toggle(!$task->isIsDone());
        $taskRepository->save($task, true);

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function deleteTaskAction(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository) : Response
    {
        $taskRepository->remove($task, true);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }
}
