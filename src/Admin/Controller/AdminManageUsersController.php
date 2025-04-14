<?php 
namespace App\Admin\Controller;

use App\Account\Enum\AccountStatus;
use App\Account\Repository\AccountRepository;
use App\Account\Service\AccountService;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminManageUsersController extends AbstractController
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }


    #[Route('/users', name: 'admin_users_list')]
    public function listUsers(UserRepository $userRepository)
    {
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
        return $this->render('admin/users.html.twig', [
            'users'=>$userData
        ]);    }
        #[Route('/user/{id}/accounts', name: 'admin_user_accounts')]
        #[IsGranted('ROLE_ADMIN')] 
        public function showUserAccounts(int $id, UserRepository $userRepository, AccountRepository $bankAccountRepository)
        {
            $user = $userRepository->find($id);
        
            if (!$user) {
                throw $this->createNotFoundException("User not found.");
            }
        
            $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);
        
            return $this->render('@Admin/userAccounts.html.twig', [
                'user' => $user,            
                'bankAccounts' => $bankAccounts,
            ]);
        }
        

    #[Route('/user/{id}/account/{accountId}/transactions', name: 'admin_account_transactions')]
    #[IsGranted('ROLE_ADMIN')] 

    public function showAccountTransactions(int $accountId): Response
    {
        $transactions = $this->accountService->getAccountTransactions($accountId);

        return $this->render('admin/userAccountTransactions.html.twig', [
            'transactions' => $transactions,
            'accountId' => $accountId,
        ]);
    }
    #[Route('/user/{userId}/account/{accountId}/toggle-status', name: 'toggle_bank_account_status')]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleStatus(int $userId, int $accountId, AccountRepository $bankAccountRepository, EntityManagerInterface $entityManager): Response
    {
        $bankAccount = $bankAccountRepository->find($accountId);
    
        if (!$bankAccount) {
            throw $this->createNotFoundException("Bank account not found.");
        }
    
        if ($bankAccount->getStatus() === AccountStatus::ACTIVE) {
            $bankAccount->setStatus(AccountStatus::CLOSE);
        } else {
            $bankAccount->setStatus(AccountStatus::ACTIVE);
        }
    
        $entityManager->flush();
    
        return $this->redirectToRoute('admin_user_accounts', ['id' => $userId]);
    }
    
}
