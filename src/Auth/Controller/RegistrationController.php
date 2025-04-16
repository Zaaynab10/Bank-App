<?php
namespace App\Auth\Controller;

use App\Auth\Entity\User;
use App\Auth\Form\RegistrationFormType;
use App\Auth\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register/customer', name: 'register_customer')]
    public function registerCustomer(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $user->setRoles(['ROLE_CUSTOMER']);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $security->login($user, AppCustomAuthenticator::class, 'main');
        }

        return $this->render('@Auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/admin', name: 'register_admin')]
    #[IsGranted('ROLE_ADMIN')]

    public function registerAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();
        $user->setRoles(['ROLE_ADMIN']);

            return $security->login($user, AppCustomAuthenticator::class, 'main');
        }

        return $this->render('@Auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
