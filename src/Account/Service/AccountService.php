<?php 
namespace App\Account\Service;

use App\Account\Repository\AccountRepository;
use App\Transactions\Repository\TransactionRepository;

class AccountService
{
    private $accountRepository;
    private $transactionRepository;

    public function __construct(AccountRepository $accountRepository, TransactionRepository $transactionRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getUserAccounts($user)
    {
        return $this->accountRepository->findBy(['owner' => $user]);
    }

    public function getAccountTransactions(int $accountId)
    {
        $transactions = $this->transactionRepository->findBy(
            ['source_account' => $accountId],
            ['date_time' => 'DESC']
        );

        $destinationTransactions = $this->transactionRepository->findBy(
            ['destination_account' => $accountId],
            ['date_time' => 'DESC']
        );

        return array_merge($transactions, $destinationTransactions);
    }
}
