<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listAction(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles()); // Vérifier si l'utilisateur a le rôle ROLE_ADMIN
    
        // Récupérer les tâches liées à l'utilisateur connecté
        $tasks = $taskRepository->findBy(['user' => $user]);
    
        // Si l'utilisateur est un administrateur, ajouter les tâches anonymes
        if ($isAdmin) {
            $anonymousTasks = $taskRepository->findBy(['user' => null]);
            $tasks = array_merge($tasks, $anonymousTasks);
        }
    
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }


    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, EntityManagerInterface $em)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtenez l'utilisateur connecté
            $user = $this->getUser();

            // Liez l'utilisateur à la tâche
            $task->setUser($user);

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

            return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, TaskRepository $taskRepository, EntityManagerInterface $em)
    {
        // dd($task);
        // var_dump($task->getTitle());
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);
            // $em->persist($task);
            // $em->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(int $id, TaskRepository $taskRepository)
    {
        // A FINIR !!!! 
        $task = $taskRepository->find($id);

        // if (!$task) {
        //     throw $this->createNotFoundException('La tâche n\'existe pas.');
        // }

        $task->toggle(!$task->isDone());
        $taskRepository->save($task, true);

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }


    #[Route("/tasks/{id}/delete", name:"task_delete")]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté avec un compte utilisateur')]
    public function deleteTaskAction(Task $task, TaskRepository $taskRepository): Response
    {
        $taskRepository->remove($task, true);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
