<?php declare(strict_types=1);
namespace App\Enums;
use Illuminate\Validation\Rules\Enum;
/**
 * @method static static CASH()
 * @method static static MOBILE()
 * @method static static ONLINE()
 */
final class   TransactionStatus extends Enum
{
    const Pending = 'pending';
    const Success = 'success';
    const Fail = 'fail';
    const Processing = 'processing';
} 