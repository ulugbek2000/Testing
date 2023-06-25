<?php declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static CASH()
 * @method static static MOBILE()
 * @method static static ONLINE()
 */
final class   TransactionType extends Enum
{
    const Doc = 'doc';
    const Video = 'video';
    const Audio = 'audio';
    const Text = 'text';
    const Image = 'image';
    const Quiz = 'quiz';
}