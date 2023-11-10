<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ADMIN()
 * @method static static TEACHER()
 * @method static static STUDENT()
 */
final class UserType extends Enum
{
    const Admin = 1;
    const Teacher = 2;
    const Student = 3;
}
