<?php

namespace App\Admin\Controller;

use App\Account\Enum\AccountStatus;
use App\Account\Repository\AccountRepository;
use App\Account\Service\AccountService;
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

final class AdminController extends AbstractController
{
    private UserSearchService $userSearchService;
    private $accountService;

    public function __construct(UserSearchService $userSearchService, AccountService $accountService)
    {
        $this->userSearchService = $userSearchService;
        $this->accountService = $accountService;
    }

    #[Route('/', name: 'admin_home')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        UserRepository        $userRepository,
        AccountRepository     $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $this->getUser();

        $users = $userRepository->findAll();
        $totalClients = $userRepository->count([]);
        $totalAccounts = $bankAccountRepository->count([]);
        $totalTransactions = $transactionRepository->count([]);
        $totalTransactionAmount = $transactionRepository->getTotalTransactionAmount();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
        }
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);

        foreach ($recentUsers as $userObj) {
            $accounts = $bankAccountRepository->findBy(['owner' => $userObj]);
            $userObj->accounts = $accounts; 
        }
    
        return $this->render('@Admin/index.html.twig', [
            'user' => $user,
            'totalClients' => $totalClients,
            'totalAccounts' => $totalAccounts,
            'totalTransactions' => $totalTransactions,
            'totalTransactionAmount' => $totalTransactionAmount,
            'users' => $userData,
            'recentUsers' => $recentUsers,
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

    #[Route('/users/{id}', name: 'admin_user_detail')]
    public function userDetail(
        int                   $id,
        UserRepository        $userRepository,
        AccountRepository     $bankAccountRepository,
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur demandé n'existe pas.");
        }

        $transactions = [];
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);

        return $this->render('@Admin/userDetail.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/users/add', name: 'admin_add_user', methods: ['POST'])]
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

        return $this->redirectToRoute('admin_home');
    }

    #[Route('/users', name: 'admin_users_list')]
    public function listUsers(UserRepository $userRepository) {
        $users = $userRepository->findAll();
        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
        }
        return $this->render('@Admin/users.html.twig', [
            'users' => $userData
        ]);
    }

    #[Route('/users/{id}/accounts', name: 'admin_user_accounts')]
    #[IsGranted('ROLE_ADMIN')]
    public function showUserAccounts(int $id, UserRepository $userRepository, AccountRepository $bankAccountRepository) {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("User not found.");
        }

        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);

        return $this->render('@Admin/accounts.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    #[Route('/accounts/{accountId}/transactions', name: 'admin_account_transactions')]
    #[IsGranted('ROLE_ADMIN')]
    public function showAccountTransactions(int $accountId): Response {
        $transactions = $this->accountService->getAccountTransactions($accountId);
        $user = $this->getUser();
        return $this->render('@Admin/transactions.html.twig', [
            'user' => $user,
            'transactions' => $transactions,
            'accountId' => $accountId,
        ]);
    }

    #[Route('/accounts/{accountId}/toggle-status', name: 'toggle_account_status')]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleStatus(int $accountId,
                                 AccountRepository $accountRepository,
                                 EntityManagerInterface $entityManager): Response {

        $account = $accountRepository->find($accountId);

        if (!$account) {
            throw $this->createNotFoundException("Bank account not found.");
        }

        if ($account->getStatus() === AccountStatus::ACTIVE) {
            $account->setStatus(AccountStatus::CLOSE);
        } else {
            $account->setStatus(AccountStatus::ACTIVE);
        }

        $entityManager->flush();

        return $this->redirectToRoute('admin_user_accounts', [
            'id' => $account->getOwner()->getId(),
        ]);
    }
}