<?php
namespace App\Account\Enum;

enum AccountType : string {
    case SAVINGS = 'savings';
    case CURRENT = 'current';
}