<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static VIDEO()
 * @method static static AUDIO()
 * @method static static TEXT()
 */
final class LessonTypes extends Enum
{

    const Video = 'video';
    const Audio = 'audio';
    const Text = 'text';
}
