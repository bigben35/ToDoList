<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function listAction(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

    #[Route('/users/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function createAction(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            // Récupérer les rôles sélectionnés
            $roles = $form->get('roles')->getData();

            // Affecter les rôles à l'utilisateur
            $user->setRoles([$roles]);

            $userRepository->save($user, true);

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function editAction(User $user, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        // if (!$this->isGranted('ROLE_ADMIN') && $user->getUser() != $this->getUser()) {
        //     throw new AccessDeniedException("Vous n'avez pas l'autorisation d'éditer cette figure !");
        // }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));

            $user->setRoles([$form->get('roles')->getData()]);

            $userRepository->save($user, true);

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
