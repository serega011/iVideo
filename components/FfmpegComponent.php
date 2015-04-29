<?php

namespace app\components;

use yii\base\Component;
use yii\base\InvalidConfigException;

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
        try
        {
            exec("ffmpeg -sameq -i {$source} {$destination}");
        }
        catch (Exception $e)
        {
            return false;
        }

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
        try
        {
            exec("ffprobe -v quiet -print_format json -show_format -show_streams {$source}", $output);
        }
        catch (Exception $e)
        {
            return false;
        }

        return $output;
    }

}
