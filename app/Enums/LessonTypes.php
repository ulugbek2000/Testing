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

    public function updateLesson(array $attributes)
    {
        if (array_key_exists('lesson_types', $attributes)) {
            if (!in_array($attributes['lesson_types'], [self::Video, self::Audio, self::Text])) {
                return new \InvalidArgumentException("Invalid lesson type");
            }
        }

        $this->update($attributes);
    }
}
