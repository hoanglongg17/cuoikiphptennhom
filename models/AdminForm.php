<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;


class AdminForm extends Model
{
    public $displayname;
    public $email;
    public $password;
    public $password_repeat;

    public function rules()
    {
        return [
            [['displayname', 'email', 'password', 'password_repeat'], 'required', 'message' => '{attribute} không được để trống.'],
            
            ['displayname', 'match', 'pattern' => '/^[\p{L}0-9 ]+$/u', 'message' => 'Họ và tên chỉ được dùng chữ cái, số và dấu cách.'],
            ['displayname', 'string', 'min' => 2, 'max' => 50, 'tooShort' => 'Tên quá ngắn.', 'tooLong' => 'Tên quá dài.'],

            ['email', 'email', 'message' => 'Định dạng email không hợp lệ.'],
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

    
    public function createAdmin()
    {
        if (!$this->validate()) {
            return null;
        }

        $admin = new User();
        $admin->displayname = $this->displayname;
        $admin->email = $this->email;
        $admin->role = 'admin';
        
        $admin->setPassword($this->password);
        $admin->createdat = date('Y-m-d H:i:s');
        $admin->currentstreak = 0;
        
        return $admin->save() ? $admin : null;
    }
}
