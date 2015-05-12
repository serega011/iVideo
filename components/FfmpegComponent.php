<?php

namespace app\components;

use yii\base\Component;

/**
 * FfmpegComponent
 *
 * Launching wrapper for ffmpeg and ffprobe
 *
 * @package app\components
 */
class FfmpegComponent extends Component
{
    /**
     * convert
     *
     * Convert media with same quality
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
     * Get info about media in JSON
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
