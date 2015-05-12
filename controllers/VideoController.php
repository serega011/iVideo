<?php

namespace app\controllers;

use Yii;
use Exception;
use app\models\Video;
use app\models\UploadedFile;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use Faker\Provider\File;

class VideoController extends ActiveController
{
    public $modelClass = 'app\models\Video';
    public $documentPath = 'upload/';

    // расширение исходного видео файла
    const EXTENSION_SOURSE = '.flv';

    // расширение преобразованного файла
    const EXTENSION_DESTINATION = '.mp4';


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // send Authorization : Basic base64(token:) in header
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => HttpBasicAuth::className()];
        return $behaviors;
    }


    /**
     * currentUserId
     *
     * Возвращает ID текущего пользователя
     *
     * @return int
     */
    private function currentUserId()
    {
        return \Yii::$app->user->getId();
    }


    /**
     * actionView
     *
     * GET /resource/{id} -> actionView -> Read the resource
     *
     * @param $id
     * @return bool|Array
     */
    public function actionView($id)
    {
        // пример функции с одной точкой выхода
        try
        {
            if (!$id)
                throw new Exception("Не выбран ID");

            $video = Video::findVideo($id, $this->currentUserId());
            if (!$video)
                throw new Exception("Не найдена запись в БД");

            $result = Yii::$app->ffmpeg->info($video->fileName);
        }
        catch (Exception $e)
        {
            // можно сохранить исключение в лог (при желании)
            // или отдать клиенту (это ведь REST API)
            $result = false;
        }

        return $result;
    }


    /**
     * actionCreate
     *
     * POST /resource -> actionCreate -> Create the resource
     *
     * @return bool|Video
     */
    public function actionCreate()
    {
        try
        {
            $uploadedFile = new UploadedFile('data');
            if (!$uploadedFile)
                throw new Exception("Ошибка загрузки файла");

            if (!$uploadedFile->checkAllowedExtension(self::EXTENSION_SOURSE))
                throw new Exception("Расширение файла не соответствует ожидаемому");

            $fileName = $this->documentPath.uniqid().self::EXTENSION_SOURSE;
            if (!$uploadedFile->upload($fileName))
                throw new Exception("Ошибка записи");
        }
        catch (Exception $e)
        {
            return false;
        }

        $newName = $this->documentPath.uniqid().self::EXTENSION_DESTINATION;
        return Video::addVideo($uploadedFile->originalName, $fileName, $newName, $this->currentUserId());
    }


    /**
     * actionUpdate
     *
     * PUT, PATCH /resource/{id} -> actionUpdate -> Update the resource
     * (convert video from flv to mp4)
     *
     * @param $id
     * @return bool|Video
     */
    public function actionUpdate($id)
    {
        $video = Video::findVideo($id, $this->currentUserId());
        if (Video::canProcess() || !$video || $video->isConverted)
            return false;

        // устанавливаем флаг конвертации
        $video->status = 1;
        $video->save();

        if (!Yii::$app->ffmpeg->convert($video->fileName, $video->newName))
        {
            $video->status = 0;
            $video->save();
            return false;
        }

        $video->isConverted = 1;
        $video->status = 0;
        $video->save();
        return $video;
    }


    /**
     * actionDelete
     *
     * DELETE /resource/{id} -> actionDelete -> Delete the resource
     *
     * @param $id
     * @param string $mode
     */
    public function actionDelete($id, $mode = 'original')
    {
        $video = Video::findVideo($id, $this->currentUserId());
        if ($video && $mode == 'original')
        {
            if (file_exists($video->fileName))
                unlink($video->fileName);

            if ($video->isConverted && file_exists($video->newName))
                unlink($video->newName);

            $video->delete();
        }
        else if ($video && $mode == 'converted')
        {
            if ($video->isConverted && file_exists($video->newName))
                unlink($video->newName);

            $video->isConverted = 0;
            $video->status = 0;
            $video->save();
        }
        else
            return false;

        return true;
    }


    /**
     * actionIndex
     *
     * GET /resource/{id} -> actionIndex -> Get file content
     *
     * @param $id
     * @param string $mode
     * @return bool|file content
     */
    public function actionIndex($id, $mode = 'original')
    {
        $video = Video::findVideo($id, $this->currentUserId());
        if ($video && $mode == 'original' && file_exists($video->fileName))
            return file_get_contents($video->fileName);
        else if ($video && $mode == 'converted' && $video->isConverted && file_exists($video->newName))
            return file_get_contents($video->newName);

        return false;
    }

}