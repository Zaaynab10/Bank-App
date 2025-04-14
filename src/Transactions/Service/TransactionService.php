<?php

namespace App\Transactions\Service;

use App\Account\Entity\Account;
use App\Transactions\Entity\Transaction;
use App\Transactions\Enum\TransactionStatus;
use App\Transactions\Enum\TransactionType;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processTransaction(
        float           $amount,
        Account         $sourceAccount,
        Account         $destinationAccount,
        TransactionType $transactionType
    ) {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType($transactionType);
        $transaction->setStatus(TransactionStatus::SUCCESSED);

        if ($transactionType === TransactionType::TRANSFER || $transactionType === TransactionType::WITHDRAWAL) {
            $transaction->setSourceAccount($sourceAccount);
            $sourceAccount->setBalance($sourceAccount->getBalance() - $amount);
        }

        if ($transactionType === TransactionType::TRANSFER || $transactionType === TransactionType::DEPOSIT) {
            $transaction->setDestinationAccount($destinationAccount);
            $destinationAccount->setBalance($destinationAccount->getBalance() + $amount);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($sourceAccount);
        $this->entityManager->persist($destinationAccount);
        $this->entityManager->flush();
    }
    public function createFailedTransaction(
        float                  $amount,
        Account                $sourceAccount,
        Account                $destinationAccount,
        TransactionType        $transactionType,
        EntityManagerInterface $entityManager
    ) {
        $transaction = new Transaction();
    
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType($transactionType);
        $transaction->setStatus(TransactionStatus::FAILED);
        $transaction->setSourceAccount($sourceAccount);
        $transaction->setDestinationAccount($destinationAccount);
    
        $entityManager->persist($transaction);
        $entityManager->flush();
    }
}
