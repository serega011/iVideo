<?php

namespace app\models;

use \yii\db\ActiveRecord;

/**
 * Video
 *
 * Video model
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
    // limit of concurrent media convertations at the same time
    const PROCESSING_LIMIT = 5;

    /**
     * tableName
     *
     * Table name
     *
     * @return String
     */
    public static function tableName()
    {
        return '{{%video}}';
    }


    /**
     * attributeLabels
     *
     * DB column names
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
     * Get the primary key
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * findById
     *
     * Get element by ID
     *
     * @param $id
     * @return null|static
     */
    private static function findById($id)
    {
        return self::findOne(['id' => $id]);
    }


    /**
     * findByUserId
     *
     * Get element by userId
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
     * Get element by id and userId
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
     * How many video files are processing now
     *
     * @return int
     */
    public static function countProcessing()
    {
        return static::find()->where('status = 1')->count();
    }


    /**
     * canProcess
     *
     * Is it possible to handle one more file at this moment
     *
     * @return int|string
     */
    public static function canProcess()
    {
        return (self::countProcessing() <= self::PROCESSING_LIMIT);
    }


    /**
     * isConverted
     *
     * Is the media already converted?
     *
     * @param $id
     * @return bool
     */
    public static function isConverted($id)
    {
        $video = self::findById($id);
        return ($video->isConverted == 1);
    }


    /**
     * addVideo
     *
     * Add a new element to DB
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


    /**
     * beforeConvertation
     *
     * This function must be called before media convertation
     *
     * @param $id
     */
    public static function beforeConvertation($id)
    {
        $video = self::findById($id);
        $video->status = 1;
        $video->save();
    }


    /**
     * afterConvertation
     *
     * This function must be called after media convertation
     *
     * @param $id
     */
    public static function afterConvertation($id)
    {
        $video = self::findById($id);
        $video->status = 0;
        $video->isConverted = 1;
        $video->save();
    }


    /**
     * afterRemoveConverted
     *
     * This function must be called after removing of the coverted media
     *
     * @param $id
     */
    public static function afterRemoveConverted($id)
    {
        $video = self::findById($id);
        $video->status = 0;
        $video->isConverted = 0;
        $video->save();
    }

}
