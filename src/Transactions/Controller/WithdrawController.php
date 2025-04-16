<?php

namespace App\Transactions\Controller;

use App\Account\Repository\AccountRepository;
use App\Transactions\Entity\Transaction;
use App\Transactions\Enum\TransactionType;
use App\Transactions\Form\WithdrawForm;
use App\Transactions\Service\TransactionService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class WithdrawController extends AbstractController {
    #[Route('/withdraw', name: 'withdraw')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function MakeWithdrawal(
        Request $request,
        TransactionService $transactionService,
        SessionInterface $session,
        AccountRepository $accountRepository
    ): Response {
        $user = $this->getUser();
        $errors = [];

        $bankAccountId = $session->get('bank_account_id');
        if (!$bankAccountId) {
            $errors[] = 'Aucun compte sélectionné dans la session.';
        }

        $bankAccount = $accountRepository->find($bankAccountId);
        if (!$bankAccount) {
            $errors[] = 'Compte introuvable.';
        } elseif ($bankAccount->getOwner() !== $user) {
            $errors[] = 'Vous n\'êtes pas propriétaire de ce compte.';
        } elseif (!$bankAccount->isActive()) {
            $errors[] = 'Le compte est inactif. Retrait refusé.';
        }

        $transaction = new Transaction();
        $transaction->setSourceAccount($bankAccount);

        $form = $this->createForm(WithdrawForm::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($errors) === 0) {
            $amount = $form->get('amount')->getData();

            if (!$bankAccount->canWithdraw($amount)) {
                $errors[] = 'Retrait refusé : fonds insuffisants ou limite dépassée.';
            } else {
                $transactionService->processTransaction(
                    $amount,
                    $bankAccount,
                    $bankAccount,
                    TransactionType::WITHDRAWAL
                );

                return $this->redirectToRoute('account', [
                    'accountId' => $bankAccount->getId(),
                ]);
            }
        }

        return $this->render('@Transactions/withdraw.html.twig', [
            'form' => $form->createView(),
            'account' => $bankAccount,
            'errors' => $errors
        ]);
    }
}




