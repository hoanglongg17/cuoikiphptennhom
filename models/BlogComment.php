<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * BlogComment Model - Bình luận bài viết
 */
class BlogComment extends ActiveRecord
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'blogcomments';
    }

    /**
     * Định nghĩa các rule xác thực
     */
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

    /**
     * Get attribute labels cho form
     */
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

    /**
     * Relationship: Bài viết mà bình luận thuộc về
     */
    public function getPost()
    {
        return $this->hasOne(BlogPost::class, ['postid' => 'postid']);
    }

    /**
     * Relationship: Người dùng tức là tác giả bình luận
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }
}
