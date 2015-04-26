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
 * @property integer    $converStartTime
 * @property integer    $convertFinishTime
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
            'converStartTime' => 'Дата начала конвертации',
            'convertFinishTime' => 'Дата завершения конвертации',
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

}
