<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;

    private $_user = false;

    /**
     * rules
     *
     * Validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }


    /**
     * validatePassword
     *
     * Validate entered password
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors())
        {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password))
                $this->addError($attribute, 'Не верный логин или пароль');
        }
    }


    /**
     * login
     *
     * Authorize user
     *
     * @return boolean
     */
    public function login()
    {
        if ($this->validate())
            return Yii::$app->user->login($this->getUser());
        else
            return false;
    }


    /**
     * getUser
     *
     * Get user by username
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false)
            $this->_user = User::findByUsername($this->username);

        return $this->_user;
    }

}
