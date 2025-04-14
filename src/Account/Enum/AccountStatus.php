<?php
namespace App\Account\Enum;

enum AccountStatus : string {
    case ACTIVE= 'active';
    case CLOSE = 'close';
}