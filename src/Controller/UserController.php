<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/users', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository, TagAwareCacheInterface $cachePool) : Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $idCache = "getUsersList";
        $usersList = $cachePool->get($idCache, function (ItemInterface $item) use ($userRepository) {
            $item->tag("usersCache");
            $cachedUsersList = $userRepository->findAll();
        
            return $cachedUsersList;
        });
        
        return $this->render('user/list.html.twig', ['users' => $usersList]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, TagAwareCacheInterface $cachePool) : Response
    {
        // $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        // $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $userRepository->save($user, true);

            $cachePool->invalidateTags(["usersCache"]);
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(#[MapEntity(id:'id')]User $user, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, TagAwareCacheInterface $cachePool) : Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $userRepository->save($user, true);
            $cachePool->invalidateTags(["usersCache"]);

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', ['form' => $form, 'user' => $user]);
    }
}
