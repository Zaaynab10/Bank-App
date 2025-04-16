<?php

namespace App\Transactions\Controller;

use App\Account\Repository\AccountRepository;
use App\Transactions\Entity\Transaction;
use App\Transactions\Enum\TransactionType;
use App\Transactions\Form\DepositForm;
use App\Transactions\Service\TransactionService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class DepositController extends AbstractController {
    #[Route('/deposit', name: 'deposit')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function MakeDeposit(
        Request $request,
        SessionInterface $session,
        TransactionService $transactionService,
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
            $errors[] = 'Le compte est inactif. Dépôt refusé.';
        }

        $transaction = new Transaction();
        $transaction->setSourceAccount($bankAccount);

        $form = $this->createForm(DepositForm::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($errors) === 0) {
            $amount = $form->get('amount')->getData();

            if (!$bankAccount->canDeposit($amount)) {
                $errors[] = 'Dépôt refusé : la limite de dépôt de 25 000 € a été dépassée.';
            } else {
                $transactionService->processTransaction(
                    $amount,
                    $bankAccount,
                    $bankAccount,
                    TransactionType::DEPOSIT
                );

                return $this->redirectToRoute('account', [
                    'accountId' => $bankAccount->getId(),
                ]);
            }
        }

        return $this->render('@Transactions/deposit.html.twig', [
            'form' => $form->createView(),
            'account' => $bankAccount,
            'errors' => $errors
        ]);
    }
}
