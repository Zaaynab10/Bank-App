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
        $errors = [];
    
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);
        $beneficiaries = $entityManager->getRepository(Beneficiary::class)->findBy(['member' => $user]);
        $transaction = new Transaction();
    
        $form = $this->createForm(TransferForm::class, $transaction, [
            'user' => $user,
            'bank_accounts' => $bankAccounts,
            'beneficiaries' => $beneficiaries,
        ]);
    
        $form->handleRequest($request);
        $sourceAccount = $bankAccountRepository->find($bankAccountId);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $destinationAccountNumber = $form->get('destination_account_number')->getData()->getBankAccountNumber();
            $amount = $form->get('amount')->getData();
    
            if ($sourceAccount === null) {
                $errors[] = 'Compte source introuvable.';
            } elseif (!$sourceAccount->canWithdraw($amount)) {
                $errors[] = 'Transfert refusé : fonds insuffisants ou limite dépassée.';
            } elseif ($sourceAccount->getOwner() !== $user) {
                $errors[] = 'Vous n\'êtes pas propriétaire du compte source.';
            } elseif (!$sourceAccount->isActive()) {
                $errors[] = 'Le compte source est inactif.';
            } else {
                $destinationAccount = $bankAccountRepository->findOneBy(['account_number' => $destinationAccountNumber]);
                if ($destinationAccount === null) {
                    $errors[] = 'Compte destinataire introuvable.';
                } elseif (!$destinationAccount->isActive()) {
                    $errors[] = 'Le compte destinataire est inactif.';
                } elseif (!$destinationAccount->canDeposit($amount)) {
                    $transactionService->createFailedTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER, $entityManager);
                    $errors[] = 'Transfert refusé : le compte épargne a dépassé sa limite de dépôt de 25 000 €.';
                } else {
                    $transactionService->processTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER);
                    return $this->redirectToRoute('account', ['accountId' => $sourceAccount->getId()]);
                }
            }
        }
    
        return $this->render('@Transactions/transfer.html.twig', [
            'form' => $form->createView(),
            'account' => $sourceAccount,
            'errors' => $errors,
        ]);
    }
    
}
