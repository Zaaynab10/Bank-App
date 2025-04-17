<?php

namespace App\Transactions\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Account\Repository\AccountRepository;
use App\Beneficiary\Entity\Beneficiary;
use App\Transactions\Entity\Transaction;
use App\Transactions\Enum\TransactionType;
use App\Transactions\Form\TransferForm;
use App\Transactions\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// ... (namespace, use, etc.)

final class TransferController extends AbstractController
{
    #[Route('/transfer', name: 'transfer')]
    #[IsGranted('ROLE_CUSTOMER')] 
    public function MakeTransfer(
        Request $request,
        AccountRepository $bankAccountRepository,
        TransactionService $transactionService, 
        EntityManagerInterface $entityManager,
        SessionInterface $session,
    ): Response {
        $user = $this->getUser();
        $bankAccountId = $session->get('bank_account_id');
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);
        $sourceAccount = $bankAccountRepository->find($bankAccountId);

        if ($sourceAccount && $sourceAccount->getType()->value === 'savings') {
            $currentAccounts = $bankAccountRepository->findBy([
                'owner' => $user,
                'type' => 'current'
            ]);
            $beneficiaries = [];
            foreach ($currentAccounts as $account) {
                $beneficiary = new Beneficiary();
                $beneficiary->setBankAccountNumber($account->getAccountNumber());
                $beneficiary->setMember($user);
                $beneficiaries[] = $beneficiary;
            }
        } elseif ($sourceAccount && $sourceAccount->getType()->value === 'current') {
            $beneficiaries = $entityManager->getRepository(Beneficiary::class)
                ->findBy(['member' => $user]);
        } else {
            $beneficiaries = [];
        }
        

        $transaction = new Transaction();

        $form = $this->createForm(TransferForm::class, $transaction, [
            'user' => $user,
            'bank_accounts' => $bankAccounts,
            'beneficiaries' => $beneficiaries,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $destinationAccountNumber = $form->get('destination_account_number')->getData()->getBankAccountNumber();
            $amount = $form->get('amount')->getData();

            if (!$sourceAccount->canWithdraw($amount)) {
                throw $this->createAccessDeniedException('Withdrawal denied, insufficient funds or limit exceeded.');
            }
        
            if ($sourceAccount === null) {
                throw $this->createNotFoundException('Source account not found.');
            }

            $destinationAccount = $bankAccountRepository->findOneBy(['account_number' => $destinationAccountNumber]);

            if ($destinationAccount === null) {
                throw $this->createNotFoundException('Destination account not found.');
            }

            if ($sourceAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own the source account.');
            }

            if (!$sourceAccount->isActive()) {
                throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
            }

            if (!$destinationAccount->isActive()) {
                throw new AccessDeniedException('Le compte destination est inactif. Transaction refusée.');
            }

            if (!$destinationAccount->canDeposit($amount)) {
                $transactionService->createFailedTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER, $entityManager);
                throw $this->createAccessDeniedException('Transfer denied: the savings account has exceeded its deposit limit of 25,000.');
            }

            $transactionService->processTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER);

            return $this->redirectToRoute('account', [
                'accountId' => $sourceAccount->getId(),
            ]);
        }

        return $this->render('@Transactions/transfer.html.twig', [
            'form' => $form->createView(),
            'account' => $sourceAccount,
        ]);
    }
}


