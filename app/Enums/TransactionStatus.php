<?php declare(strict_types=1);
namespace App\Enums;
use Illuminate\Validation\Rules\Enum;
/**
 * @method static static PANDING()
 * @method static static SUCCESS()
 * @method static static FAIL()
 * @method static static PROCESSING()
 */
final class   TransactionStatus extends Enum
{
    const Pending = 'pending';
    const Success = 'success';
    const Fail = 'fail';
    const Processing = 'processing';
} 