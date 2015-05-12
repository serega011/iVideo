<?php

namespace app\components;

use yii\base\Component;

/**
 * FfmpegComponent
 *
 * Обертка запуска ffmpeg и ffprobe
 *
 * @package app\components
 */
class FfmpegComponent extends Component
{
    /**
     * convert
     *
     * Конвертация медиа файла без изменения размеров и качества
     *
     * @param $source
     * @param $destination
     * @return bool
     */
    public function convert($source, $destination)
    {
        exec("ffmpeg -sameq -i {$source} {$destination}");
        return true;
    }


    /**
     * info
     *
     * Получить информацию о медиа файле в формате JSON
     *
     * @param $source
     * @return bool
     */
    public function info($source)
    {
        exec("ffprobe -v quiet -print_format json -show_format -show_streams {$source}", $output);
        return $output;
    }

}
