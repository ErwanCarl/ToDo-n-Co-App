<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/tasks', name: 'task_')]
class TaskController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TaskRepository $taskRepository) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findUserTasks()]);
    }

    #[Route('/done', name: 'list_done', methods: ['GET'])]
    public function listDone(TaskRepository $taskRepository) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('task/list-done.html.twig', ['tasks' => $taskRepository->findUserTasksDone()]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
     public function create(Request $request, TaskRepository $taskRepository) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
     public function edit(#[MapEntity(id:'id')]Task $task, Request $request, TaskRepository $taskRepository) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

    #[Route('/{id}/toggle', name: 'toggle', methods: ['GET'])]
    public function toggleTask(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository) : Response
    {
        $this->denyAccessUnlessGranted('toggle', $task);

        $task->toggle(!$task->isIsDone());
        $taskRepository->save($task, true);

        if($task->isIsDone() == true) {
            $this->addFlash('success', sprintf("La tâche '%s' a bien été marquée comme faite.", $task->getTitle()));
        } else {
            $this->addFlash('success', sprintf("La tâche '%s' a bien été replacé dans les tâches à faire.", $task->getTitle()));
        }
        
        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function deleteTask(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository, Request $request) : Response
    {
        $this->denyAccessUnlessGranted('delete', $task);

        if ($this->isCsrfTokenValid(sprintf('delete%s', $task->getId()), $request->request->get('_token'))) {
            $taskRepository->remove($task, true);
            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }
        
        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }
}
