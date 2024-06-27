<?php

namespace App\Controller\User;

use App\Form\ChangePasswordFormType;
use App\Form\ChangeProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller managing user profile editing and control panel actions.
 */
class EditController extends AbstractController
{

    /**
     * Renders the user control panel.
     *
     * @return Response
     */
    #[Route('/user/cp', name: 'app_user_cp')]
    public function cp(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/edit/control_panel.html.twig', [
            'controller_name' => 'EditController',
        ]);
    }

    /**
     * Handles user profile editing.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/user/change-profile', name: 'app_user_change_profile')]
    public function changeProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $form = $this->createForm(ChangeProfileFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setName(trim($user->getName()) ?: '');
            $user->setLocation(trim($user->getLocation()) ?: '');
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_user_change_profile');
        }
        return $this->render('user/edit/change_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handles password change for the current user.
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/user/change-password', name: 'app_user_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, $form->get('currentPassword')->getData())) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('newPassword')->getData()));
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Password changed successfully.');
                return $this->redirectToRoute('app_user_change_password');
            }
            else {
                $form->get('currentPassword')->addError(new FormError('Invalid current password.'));
            }
        }
        return $this->render('user/edit/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
