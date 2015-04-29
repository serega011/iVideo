<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Class UploadedFile
 *
 * Рутинные действия для загрузки файла на сервер
 *
 * @package app\models
 */
class UploadedFile extends Model
{
    public $originalName;


    /**
     * __construct
     *
     * Конструктор проверят передан ли файл и не возникла ли ошибка
     *
     * @param array $name
     */
    public function __construct($name)
    {
        $this->originalName = $name;
        if (isset($_FILES[$name]) && $_FILES[$name]['error'] != UPLOAD_ERR_NO_FILE )
            return true;
        else
            return false;
    }


    /**
     * checkAllowedExtension
     *
     * Проверка соответствует ли расширение файла ожидаемому
     *
     * @param $extension
     * @return bool
     */
    public function checkAllowedExtension($extension)
    {
        if ($extension == strtolower(strrchr($_FILES[$this->originalName]['name'], '.')))
            return true;
        else
            return false;
    }


    /**
     * upload
     *
     * Загрузка файла на сервер
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
                throw new Exception("Не удалось открыть входящий файл для чтения");

            $fp = fopen($newName, 'w');
            if (!$fp)
                throw new Exception("Не удалось открыть файл для записи");

            while ($data = fread($postdata, 1024))
                fwrite($fp, $data);

            fclose($fp);
            fclose($postdata);
        }
        catch (Exception $e)
        {
            return false;
        }

        return true;

    }

}
