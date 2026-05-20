<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;


class SignupForm extends Model
{
    public $displayname;
    public $email;
    public $password;
    public $password_repeat;

    public function rules()
    {
        return [
            [['displayname', 'email', 'password', 'password_repeat'], 'required', 'message' => '{attribute} không được để trống.'],
            
            
            ['displayname', 'match', 'pattern' => '/^[a-zA-Z0-9 ]+$/', 'message' => 'Họ và tên chỉ được dùng chữ cái không dấu và số.'],
            ['displayname', 'string', 'min' => 2, 'max' => 50, 'tooShort' => 'Tên quá ngắn.', 'tooLong' => 'Tên quá dài.'],

            
            ['email', 'email', 'message' => 'Định dạng email không hợp lệ.'],
            ['email', 'match', 'pattern' => '/^[a-z0-9](\.?[a-z0-9]){5,}@gmail\.com$/', 'message' => 'Tài khoản phải là địa chỉ @gmail.com hợp lệ.'],
            
            
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Email này đã được sử dụng.'],

            
            ['password', 'string', 'min' => 6, 'message' => 'Mật khẩu phải từ 6 ký tự trở lên.'],
            ['password', 'match', 'pattern' => '/^[a-zA-Z0-9]+$/', 'message' => 'Mật khẩu chỉ được chứa chữ cái không dấu và số.'],
            
            
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Xác nhận mật khẩu không khớp.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'displayname' => 'Họ và tên',
            'email' => 'Địa chỉ Email',
            'password' => 'Mật khẩu',
            'password_repeat' => 'Xác nhận mật khẩu',
        ];
    }

    
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->displayname = $this->displayname;
        $user->email = $this->email;
        
        $user->setPassword($this->password);

        
        
        $user->createdat = date('Y-m-d H:i:s');
        $user->currentstreak = 0;
        return $user->save() ? $user : null;
    }
}