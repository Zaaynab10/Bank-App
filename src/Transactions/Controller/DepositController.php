<?php

namespace App\Transactions\Controller;

use App\Account\Repository\AccountRepository;
use App\Transactions\Entity\Transaction;
use App\Transactions\Enum\TransactionType;
use App\Transactions\Form\DepositForm;
use App\Transactions\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
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
        Request                $request,
        EntityManagerInterface $entityManager,
        TransactionService     $transactionService,
        AccountRepository      $accountRepository

    ): Response {
        $user = $this->getUser();

        $transaction = new Transaction();

        $form = $this->createForm(DepositForm::class, $transaction, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountIndex = (int)$form->get('account')->getData();
            $amount = $form->get('amount')->getData();
            $accounts = $accountRepository->findBy([], ['id' => 'ASC']);
            $account = $accounts[$accountIndex];

            if (!$account->isActive()) {
                throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
            }

            if (!$account || $account->getOwner() !== $user) {
                $transactionService->createFailedTransaction($amount, $account, $account, TransactionType::DEPOSIT, $entityManager);

                throw $this->createAccessDeniedException('You do not own this account.');
            }


            if (!$account->canDeposit($amount)) {
                throw $this->createAccessDeniedException('Deposit denied, the deposit limit is 25,000.');
            }

            $transactionService->processTransaction($amount, $account, $account, TransactionType::DEPOSIT);

            return $this->redirectToRoute('account', [
                'accountId' => $account->getId(),
            ]);
        }

        return $this->render('@Transactions/deposit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
