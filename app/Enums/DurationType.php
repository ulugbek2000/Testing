<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static WEEK()
 * @method static static MONTH()
 * @method static static YEAR()
 */
enum   DurationType: int
{
    case Week = 1;
    case Month = 2;
    case Year = 3;
    public function selectWeek(): bool
    {
        return $this === self::Week;
    }
    public function selectMonth(): bool
    {
        return $this === self::Month;
    }
    public function selectYear(): bool
    {
        return $this === self::Year;
    }

    public function getDurationText(): string
    {
        return match ($this) {
            self::Week => 'Week',
            self::Month => 'Month',
            self::Year => 'Year',
        };
    }
}
