<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class BlogComment extends ActiveRecord
{
    
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'blogcomments';
    }

    
    public function rules()
    {
        return [
            [['postid', 'userid', 'content'], 'required'],
            [['postid', 'userid'], 'integer'],
            [['content'], 'string', 'min' => 3],
            [['status'], 'in', 'range' => ['pending', 'approved', 'rejected']],
            [['createdat', 'updatedat'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'commentid' => 'ID Bình Luận',
            'postid' => 'ID Bài Viết',
            'userid' => 'ID Người Dùng',
            'content' => 'Nội Dung',
            'status' => 'Trạng Thái',
            'createdat' => 'Ngày Tạo',
            'updatedat' => 'Cập Nhật Lần Cuối',
        ];
    }

    
    public function getPost()
    {
        return $this->hasOne(BlogPost::class, ['postid' => 'postid']);
    }

    
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }
}
