<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Class UploadedFile
 *
 * Routine functions to download a file to server
 *
 * @package app\models
 */
class UploadedFile extends Model
{
    public $originalName;


    /**
     * __construct
     *
     * Constructor will check whether a file is transferred or if an error occurred
     *
     * @param array $name
     */
    public function __construct($name)
    {
        $this->originalName = $name;
        return (isset($_FILES[$name]) && $_FILES[$name]['error'] != UPLOAD_ERR_NO_FILE);
    }


    /**
     * checkAllowedExtension
     *
     * Check whether the file extension the same as expected
     *
     * @param $extension
     * @return bool
     */
    public function checkAllowedExtension($extension)
    {
        return ($extension == strtolower(strrchr($_FILES[$this->originalName]['name'], '.')));
    }


    /**
     * upload
     *
     * Upload a new file to server
     *
     * @param $newName
     * @return bool
     */
    public function upload($newName)
    {
        try
        {
            $postdata = fopen($_FILES[$this->originalName]['tmp_name'], 'r');
            if (!$postdata)
                throw new VideoUploadException("Unable to open the input file for reading");

            $fp = fopen($newName, 'w');
            if (!$fp)
                throw new VideoUploadException("Could not open file for writing");

            while ($data = fread($postdata, 1024))
                fwrite($fp, $data);

            fclose($fp);
            fclose($postdata);
        }
        catch (VideoUploadException $e)
        {
            return $e->getMessage();
        }

        return true;

    }

}
