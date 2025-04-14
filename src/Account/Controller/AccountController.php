<?php

namespace App\Account\Controller;

use App\Account\Entity\Account;
use App\Account\Enum\AccountStatus;
use App\Account\Enum\AccountType;
use App\Account\Form\AccountForm as BankAccountFormType;
use App\Account\Repository\AccountRepository;
use App\Account\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{

    #[Route('/create', name: 'create_account', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, AccountRepository $accountRepository): Response
    {
        $accounts = $accountRepository->findBy(['owner' => $this->getUser()]);
        if (count($accounts) >= 5) {
            throw $this->createAccessDeniedException('You can only have up to 5 bank accounts');
        }

        $isSavingsAccount = $request->get('type') === AccountType::SAVINGS->value;
        if ($isSavingsAccount) {
            $hasSufficientBalance = $this->hasValidCurrentAccount($accounts);
            if (!$hasSufficientBalance) {
                throw $this->createAccessDeniedException('You must have at least one current account with a balance of 10 or more to create a savings account');
            }
        }

        $account = new Account();
        $form = $this->createForm(BankAccountFormType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account->setOwner($this->getUser());

            $accountNumber = rand(1000000000, 9999999999);
            $account->setAccountNumber((string)$accountNumber);

            $account->setBalance(100);

            $account->setStatus(AccountStatus::ACTIVE);

            $entityManager->persist($account);
            $entityManager->flush();

            return $this->redirectToRoute('accounts');
        }

        return $this->render('@Account/createAccount.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function hasValidCurrentAccount(array $bankAccounts): bool
    {
        foreach ($bankAccounts as $account) {
            if ($account->getType() === AccountType::CURRENT && $account->getBalance() >= 10) {
                return true;
            }
        }
        return false;
    }

    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Route('/', name: 'accounts')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function showUserAccounts(): Response
    {
        $user = $this->getUser();
        $bankAccounts = $this->accountService->getUserAccounts($user);

        return $this->render('@Account/accounts.html.twig', [
            'accounts' => $bankAccounts,
        ]);
    }

    #[Route('/{accountId}', name: 'account')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function showAccountTransactions(int $accountId): Response
    {
        $transactions = $this->accountService->getAccountTransactions($accountId);

        return $this->render('@Account/account.html.twig', [
            'transactions' => $transactions,
            'accountId' => $accountId,
        ]);
    }
}
