<?php

namespace App\Admin\Controller;

use App\Account\Repository\AccountRepository;
use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\UserSearchService;
use App\Transactions\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminDashController extends AbstractController
{
    private UserSearchService $userSearchService;

    public function __construct(UserSearchService $userSearchService)
    {
        $this->userSearchService = $userSearchService;
    }

    #[Route('/dash', name: 'home_admin_dash')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        UserRepository        $userRepository,
        AccountRepository     $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $this->getUser(); 

        $totalClients = $userRepository->count([]);
        $totalAccounts = $bankAccountRepository->count([]);
        $totalTransactions = $transactionRepository->count([]);
        $totalTransactionAmount = $transactionRepository->getTotalTransactionAmount();

        return $this->render('@Admin/index.html.twig', [
            'user' => $user,
            'totalClients' => $totalClients,
            'totalAccounts' => $totalAccounts,
            'totalTransactions' => $totalTransactions,
            'totalTransactionAmount' => $totalTransactionAmount,
        ]);
    }

    #[Route('/search', name: 'admin_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        $users = $this->userSearchService->searchUsers($query);

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getFirstName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/user/{id}', name: 'admin_user_detail')]
    public function userDetail(
        int                   $id,
        UserRepository        $userRepository,
        AccountRepository     $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur demandé n'existe pas.");
        }

        $transactions = $transactionRepository->findTransactionsByUserId($id);
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);

        return $this->render('admin/userDetail.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/add-user', name: 'admin_add_user', methods: ['POST'])]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setEmail($request->request->get('email'));
        $user->setPhone($request->request->get('phone'));
        $user->setRoles([$request->request->get('roles')]);

        $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur ajouté avec succès !');

        return $this->redirectToRoute('home_admin_dash');
    }
}
