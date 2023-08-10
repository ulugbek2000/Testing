<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static ADMIN()
 * @method static static TEACHER()
 * @method static static STUDENT()
 */
final class   UserType extends Enum
{
    const Admin = 'admin';
    const Teacher = 'teacher';
    const Student = 'student';
}
