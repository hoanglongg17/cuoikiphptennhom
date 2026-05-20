<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class BlogNestedComment extends ActiveRecord
{
    public static function tableName()
    {
        return 'blog_nested_comments';
    }

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function rules()
    {
        return [
            [['postid', 'userid', 'content'], 'required'],
            [['postid', 'userid', 'parentcommentid'], 'integer'],
            [['content'], 'string', 'min' => 1],
            [['status'], 'in', 'range' => ['pending', 'approved', 'rejected']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'commentid' => 'Comment ID',
            'postid' => 'Post ID',
            'userid' => 'User ID',
            'parentcommentid' => 'Parent Comment ID',
            'content' => 'Nội Dung',
            'status' => 'Trạng Thái',
            'createdat' => 'Ngày Tạo',
            'updatedat' => 'Cập Nhật',
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

    
    public function getParentComment()
    {
        return $this->hasOne(self::class, ['commentid' => 'parentcommentid']);
    }

    
    public function getReplies()
    {
        return $this->hasMany(self::class, ['parentcommentid' => 'commentid'])
            ->where(['status' => self::STATUS_APPROVED]);
    }

    
    public function getReplyCount()
    {
        return $this->hasMany(self::class, ['parentcommentid' => 'commentid'])
            ->where(['status' => self::STATUS_APPROVED])
            ->count();
    }

    
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    
    public static function findTopLevel($postid)
    {
        return static::find()
            ->where(['postid' => $postid, 'parentcommentid' => null])
            ->where(['status' => self::STATUS_APPROVED])
            ->orderBy(['createdat' => SORT_DESC]);
    }
}
