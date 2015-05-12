<?php

namespace app\controllers;

use Yii;
use app\models\Video;
use app\models\UploadedFile;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use Faker\Provider\File;

class VideoController extends ActiveController
{
    public $modelClass = 'app\models\Video';
    public $documentPath = 'upload/';

    // sourse media file extension
    const EXTENSION_SOURSE = '.flv';

    // destination media file extension
    const EXTENSION_DESTINATION = '.mp4';

    // mode to work with original (uploaded) media
    const MODE_ORIGINAL = 'original';

    // mode to work with converted (uploaded) media
    const MODE_CONVERTED = 'converted';


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
     * Get the current user ID
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
        try
        {
            if (!$id)
                throw new ActionViewException("Не выбран ID");

            $video = Video::findVideo($id, $this->currentUserId());
            if (!$video)
                throw new ActionViewException("Не найдена запись в БД");

            $result = Yii::$app->ffmpeg->info($video->fileName);
        }
        catch (ActionViewException $e)
        {
            $result = $e->getMessage();
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
                throw new ActionCreateException("Ошибка загрузки файла");

            if (!$uploadedFile->checkAllowedExtension(self::EXTENSION_SOURSE))
                throw new ActionCreateException("Расширение файла не соответствует ожидаемому");

            $fileName = $this->documentPath.uniqid().self::EXTENSION_SOURSE;
            $uloadResult = $uploadedFile->upload($fileName);
            if ($uloadResult !== true)
                throw new ActionCreateException($uloadResult);
        }
        catch (ActionCreateException $e)
        {
            return $e->getMessage();
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
     * @return bool|String
     */
    public function actionUpdate($id)
    {
        try
        {
            $video = Video::findVideo($id, $this->currentUserId());
            if (!$video)
                throw new ActionUpdateException("Видео не найдено");

            if (Video::isConverted($id))
                throw new ActionUpdateException("Видео уже сконвертировано");

            if (!Video::canProcess())
                throw new ActionUpdateException("Превышен лимит одновременной обработки файлов");

            Video::beforeConvertation($id);
            Yii::$app->ffmpeg->convert($video->fileName, $video->newName);
            Video::afterConvertation($id);
        }
        catch (ActionCreateException $e)
        {
            return $e->getMessage();
        }

        return true;
    }


    /**
     * actionDelete
     *
     * DELETE /resource/{id} -> actionDelete -> Delete the resource
     *
     * @param $id
     * @param string $mode
     */
    public function actionDelete($id, $mode = self::MODE_ORIGINAL)
    {
        $video = Video::findVideo($id, $this->currentUserId());
        if ($video && $mode == self::MODE_ORIGINAL)
        {
            if (file_exists($video->fileName))
                unlink($video->fileName);

            if ($video->isConverted && file_exists($video->newName))
                unlink($video->newName);

            $video->delete();
        }
        else if ($video && $mode == MODE_CONVERTED)
        {
            if ($video->isConverted && file_exists($video->newName))
                unlink($video->newName);

            Video::afterRemoveConverted($id);
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
    public function actionIndex($id, $mode = self::MODE_ORIGINAL)
    {
        $video = Video::findVideo($id, $this->currentUserId());
        if ($video && $mode == self::MODE_ORIGINAL && file_exists($video->fileName))
            $result = file_get_contents($video->fileName);
        else if ($video && $mode == MODE_CONVERTED && Video::isConverted($id) && file_exists($video->newName))
            $result = file_get_contents($video->newName);
        else
            $result = false;

        return $result;
    }

}