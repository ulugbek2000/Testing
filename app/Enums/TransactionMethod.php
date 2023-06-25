<?php declare (strict_types=1);
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static CASH()
 * @method static static MOBILE()
 * @method static static ONLINE()
 */
final class  TransactionMethod extends Enum
{
    const Cash = 'cash';
    const Mobile = 'mobile';
    const Online = 'online';
}