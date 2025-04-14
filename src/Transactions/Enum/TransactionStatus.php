<?php
namespace App\Transactions\Enum;

enum TransactionStatus: string
{
    case SUCCESSED = 'successed';
    case FAILED = 'failed';
}