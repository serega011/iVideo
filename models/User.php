<?php

namespace app\models;

use \yii\db\ActiveRecord;
use \yii\web\IdentityInterface;

/**
 * User
 *
 * Модель пользователя
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $token
 */
class User extends ActiveRecord implements IdentityInterface
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
        return '{{%user}}';
    }


    /**
     * rules
     *
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 32, 'min' => 4],
            [['username', 'password'], 'match', 'pattern' => '/^[a-zA-Z0-9]+$/', 'message' => 'Допускаются только буквы английского алфавита и цифры'],
        ];
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
            'username' => 'Логин',
            'password' => 'Пароль',
            'token' => 'API токен',
        ];
    }

    /**
     * findIdentity
     *
     * Поиск по ключу
     *
     * @param int|string $id
     * @return null|IdentityInterface|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }


    /**
     * findIdentityByAccessToken
     *
     * Поиск по идентификационному токену
     *
     * @param mixed $token
     * @param null $type
     * @return null|IdentityInterface|static
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // API авторизация: отправить в header:
        // Authorization : Basic base64(token:)
        // для каждого API запроса
        return static::findOne(['token' => $token]);
    }


    /**
     * getId
     *
     * Получить первичный ключ
     *
     * @return int|mixed|string
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * getAuthKey
     *
     * Получить ключ авторизации
     *
     * @return mixed|string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }


    /**
     * validateAuthKey
     *
     * Проверить ключ авторизации
     *
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    /**
     * findByUsername
     *
     * Получить запись по username
     *
     * @param $username
     * @return null|static
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }


    /**
     * validatePassword
     *
     * Проверить пароль
     *
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

}
