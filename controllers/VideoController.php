<?php

namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;

class VideoController extends ActiveController
{
    public $modelClass = 'app\models\Video';
    public $documentPath = 'upload/';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // send Authorization : Basic base64(token:) in header
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
        ];
        return $behaviors;
    }


    /**
     * @inheritdoc
     */
    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs['upload'] = ['POST'];
        return $verbs;
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
        if (!$id)
            return false;

        $video = Video::find()->where(['id' => $id, 'userId' => \Yii::$app->user->getId()])->one();
        if (!$video)
            return false;

        try
        {
            exec("ffprobe -v quiet -print_format json -show_format -show_streams {$video->fileName}", $output);
            return $output;
        }
        catch (Exception $e)
        {
            return false;
        }
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
        $postdata = fopen($_FILES['data']['tmp_name'], 'r');
        $extension = strtolower(substr($_FILES['data']['name'], strrpos($_FILES['data']['name'], '.')));
        if ($extension != '.flv')
            return false;

        $filename = $this->documentPath.uniqid().$extension;

        $fp = fopen($filename, 'w');
        while ($data = fread($postdata, 1024))
            fwrite($fp, $data);

        fclose($fp);
        fclose($postdata);

        // сохраним в БД запись о файле
        $result = new Video();
        $result->originalName = $_FILES['data']['name'];
        $result->fileName = $filename;
        $result->isConverted = false;
        $result->createTime = time();
        $result->userId = \Yii::$app->user->getId();
        $result->save();
        return $result;
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
        // сколько файлов обрабатывается в текущий момент
        $count = Video::find()
            ->where('converStartTime > 0 AND convertFinishTime = 0')
            ->count();

        if ($count >= 5)
            return false;

        $video = Video::find()->where(['id' => $id, 'userId' => \Yii::$app->user->getId()])->one();
        if (!$video || $video->isConverted)
            return false;

        // начинаем конвертацию
        $video->converStartTime = time();
        $video->save();

        $newName = str_replace('.flv', '.mp4', $video->fileName);

        // отправляем команду через exec
        try
        {
            // remuxing file from flv to mp4
            // ffmpeg -i *.flv -acodec copy -vcodec copy *.mp4

            // convert with same quality
            exec("ffmpeg -sameq -i {$video->fileName} {$newName}");
        }
        catch (Exception $e)
        {
            $video->converStartTime = 0;
            $video->save();
            return false;
        }

        // закончили конвертацию
        $video->newName = $newName;
        $video->isConverted = 1;
        $video->convertFinishTime = time();
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
        $video = Video::find()->where(['id' => $id, 'userId' => \Yii::$app->user->getId()])->one();
        if (!$video)
            return false;

        if ($mode == 'original')
        {
            if (file_exists($video->fileName))
                unlink($video->fileName);

            if (file_exists($video->newName))
                unlink($video->newName);

            $video->delete();
        }
        else if ($mode == 'converted')
        {
            if (file_exists($video->newName))
                unlink($video->newName);

            $video->newName = null;
            $video->isConverted = 0;
            $video->converStartTime = 0;
            $video->convertFinishTime = 0;
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
        $video = Video::find()->where(['id' => $id, 'userId' => \Yii::$app->user->getId()])->one();
        if (!$video)
            return false;

        if ($mode == 'original')
        {
            if (file_exists($video->fileName))
                return file_get_contents($video->fileName);
            else
                return false;
        }
        else if ($mode == 'converted')
        {
            if (file_exists($video->newName))
                return file_get_contents($video->newName);
            else
                return false;
        }
        else
            return false;
    }

}