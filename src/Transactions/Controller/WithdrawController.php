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
        Request                $request,
        TransactionService     $transactionService,
        SessionInterface $session,

        AccountRepository      $accountRepository

    ):Response {
    $user = $this->getUser();

    $bankAccountId = $session->get('bank_account_id');
    if (!$bankAccountId) {
        throw $this->createAccessDeniedException('No bank account selected in the session.');
    }

    $bankAccount = $accountRepository->find($bankAccountId);
    if (!$bankAccount) {
        throw $this->createAccessDeniedException('Bank account not found.');
    }

    if ($bankAccount->getOwner() !== $user) {
        throw $this->createAccessDeniedException('You do not own this account.');
    }

    if (!$bankAccount->isActive()) {
        throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
    }

    $transaction = new Transaction();
    $transaction->setSourceAccount($bankAccount); // Définir le compte automatiquement comme source

    $form = $this->createForm(WithdrawForm::class, $transaction);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $amount = $form->get('amount')->getData();

        if (!$bankAccount->canWithdraw($amount)) {
            throw $this->createAccessDeniedException('Withdrawal denied, insufficient funds or limit exceeded.');
        }

        $transactionService->processTransaction($amount, $bankAccount, $bankAccount, TransactionType::WITHDRAWAL);

        return $this->redirectToRoute('account', [
            'accountId' => $bankAccountId,
        ]);    }
    return $this->render('@Transactions/withdraw.html.twig', [
        'form' => $form->createView(),
    ]);
}

}

