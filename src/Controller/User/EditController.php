<?php

namespace App\Controller\User;

use App\Form\ChangeProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}
