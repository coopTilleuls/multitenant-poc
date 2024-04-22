<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(#[CurrentUser] ?User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        $newUser = new User();
        $form = $this->createForm(RegistrationFormType::class, $newUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newUser->setPassword(
                $userPasswordHasher->hashPassword(
                    $newUser,
                    $form->get('plainPassword')->getData()
                )
            );

            $newUser->setOwner($user);

            $entityManager->persist($newUser);
            $entityManager->flush();

            $this->addFlash('success', 'You have successfully created a new user!');
        }

        return $this->render('user/index.html.twig', [
            'id' => $user->getId(),
            'registrationForm' => $form,
        ]);
    }
}
