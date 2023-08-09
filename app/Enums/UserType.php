<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static CASH()
 * @method static static MOBILE()
 * @method static static ONLINE()
 */
final class   UserType extends Enum
{
    const Admin = 'admin';
    const Teacher = 'teacher';
    const Student = 'student';
}
