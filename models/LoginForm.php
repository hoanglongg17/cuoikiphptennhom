<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;


class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    /** @var User|null */
    private ?User $_user = null;

    public function rules()
    {
        return [
            [['email', 'password'], 'required', 'message' => '{attribute} không được để trống.'],
            ['email', 'email', 'message' => 'Định dạng email không hợp lệ.'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Địa chỉ Email',
            'password' => 'Mật khẩu',
            'rememberMe' => 'Ghi nhớ đăng nhập',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Email hoặc mật khẩu không chính xác.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    protected function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByEmail($this->email);
        }
        return $this->_user;
    }
}