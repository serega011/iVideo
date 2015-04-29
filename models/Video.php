<?php

namespace app\models;

use \yii\db\ActiveRecord;

/**
 * Video
 *
 * Модель видео
 *
 * @property integer    $id
 * @property string     $originalName
 * @property string     $fileName
 * @property string     $newName
 * @property boolean    $isConverted
 * @property integer    $createTime
 * @property boolean    $status
 * @property integer    $userId
 */
class Video extends ActiveRecord
{
    /**
     * tableName
     *
     * Имя таблицы в БД
     *
     * @return Имя таблицы в БД
     */
    public static function tableName()
    {
        return '{{%video}}';
    }


    /**
     * attributeLabels
     *
     * Имена полей БД
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'originalName' => 'Имя загружаемого файла',
            'fileName' => 'Имя загруженного файла',
            'newName' => 'Имя сконвертированного файла',
            'isConverted' => 'Сконвертирован ли фильм',
            'createTime' => 'Дата загрузки файла',
            'status' => 'Флаг конвертации',
            'userId' => 'ID пользователя',
        ];
    }


    /**
     * getId
     *
     * Получить первичный ключ
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * findByUserId
     *
     * Получить записи по userId
     *
     * @param $userId
     * @return \yii\db\ActiveQuery
     */
    public static function findByUserId($userId)
    {
        return static::find(['userId' => $userId]);
    }


    /**
     * findVideo
     *
     * Получить видео по id и userId
     *
     * @param $id
     * @param $userId
     * @return ActiveRecord|null
     */
    public static function findVideo($id, $userId)
    {
        return static::find()->where(['id' => $id, 'userId' => $userId])->one();
    }


    /**
     * countProcessing
     *
     * Сколько видео файлов обрабатывается
     *
     * @return int
     */
    public static function countProcessing()
    {
        return static::find()->where('status = 1')->count();
    }


    /**
     * addVideo
     *
     * Добавить новое видео в БД
     *
     * @param $originalName
     * @param $fileName
     * @param $newName
     * @param $userId
     * @return Video
     */
    public static function addVideo($originalName, $fileName, $newName, $userId)
    {
        $result = new Video();
        $result->originalName = $originalName;
        $result->fileName = $fileName;
        $result->newName = $newName;
        $result->isConverted = false;
        $result->createTime = time();
        $result->userId = $userId;
        $result->save();
        return $result;
    }
}
