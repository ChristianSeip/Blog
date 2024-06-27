<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{

    private VerifyEmailHelperInterface $verifyEmailHelper;
    private RequestStack $requestStack;
    private EmailVerifier  $emailVerifier;

    public function __construct(VerifyEmailHelperInterface $helper, RequestStack $requestStack, EmailVerifier $emailVerifier)
    {
        $this->verifyEmailHelper = $helper;
        $this->requestStack = $requestStack;
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/user/registration', name: 'app_user_registration')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_index');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->getConnection()->beginTransaction();
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
                $user->setUsername($form->get('username')->getData());
                $user->setRoles(['ROLE_USER']);
                $user->setVerified(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, (new TemplatedEmail())->htmlTemplate('user/registration/confirmation_email.html.twig'));
                $entityManager->getConnection()->commit();
                $this->addFlash('success', 'Your registration was successful. Please check your email to activate your account.');
                return $this->redirectToRoute('app_register_success');
            }
            catch (\RuntimeException $e) {
                $entityManager->getConnection()->rollBack();
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_user_registration');
            }
        }
        return $this->render('user/registration/register.html.twig', ['registrationForm' => $form->createView()]);
    }

    #[Route('/user/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');
        if (null === $id) {
            return $this->redirectToRoute('app_user_registration');
        }
        $user = $entityManager->getRepository(User::class)->find($id);
        if (null === $user) {
            return $this->redirectToRoute('app_user_registration');
        }
        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
            $user->setVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_user_registration');
        }
        $this->addFlash('success', 'Your email address has been successfully verified. You can login now.');
        return $this->redirectToRoute('app_register_success');
    }

    #[Route('/user/registration/success', name: 'app_register_success')]
    public function registerSuccess(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        if (!$session->getFlashBag()->has('success')) {
            return $this->redirectToRoute('app_user_registration');
        }
        return $this->redirectToRoute('app_index');
    }
}
