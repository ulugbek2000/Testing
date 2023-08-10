<?php 
declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

/**
 * @method static static DOC()
 * @method static static VIDEO()
 * @method static static AUDIO()
 * @method static static TEXT()
 * @method static static IMAGE()
 * @method static static QUIZ()
 */
final class   LessonType extends Enum
{
    const Doc = 'doc';
    const Video = 'video';
    const Audio = 'audio';
    const Text = 'text';
    const Image = 'image';
    const Quiz = 'quiz';
}