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


#[Route('/tasks')]
class TaskController extends AbstractController
{
    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(TaskRepository $taskRepository) : Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findAll()]);
    }

    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
     public function create(Request $request, TaskRepository $taskRepository) : Response
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

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
     public function edit(#[MapEntity(id:'id')]Task $task, Request $request, TaskRepository $taskRepository) : Response
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

    #[Route('/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTask(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository) : Response
    {
        $task->toggle(!$task->isIsDone());
        $taskRepository->save($task, true);

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function deleteTask(#[MapEntity(id:'id')]Task $task, TaskRepository $taskRepository, Request $request) : Response
    {
        if ($this->isCsrfTokenValid(sprintf('delete%s', $task->getId()), $request->request->get('_token'))) {
            $taskRepository->remove($task, true);
            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }
        
        return $this->redirectToRoute('task_list', [], Response::HTTP_SEE_OTHER);
    }
}
