<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * BlogNestedComment Model - Bình luận lồng (có trả lời)
 */
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

    /**
     * Lấy comment cha (nếu là reply)
     */
    public function getParentComment()
    {
        return $this->hasOne(self::class, ['commentid' => 'parentcommentid']);
    }

    /**
     * Lấy tất cả replies cho comment này
     */
    public function getReplies()
    {
        return $this->hasMany(self::class, ['parentcommentid' => 'commentid'])
            ->where(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Lấy count replies
     */
    public function getReplyCount()
    {
        return $this->hasMany(self::class, ['parentcommentid' => 'commentid'])
            ->where(['status' => self::STATUS_APPROVED])
            ->count();
    }

    /**
     * Kiểm tra approved status
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Lấy top-level comments (không có parent)
     */
    public static function findTopLevel($postid)
    {
        return static::find()
            ->where(['postid' => $postid, 'parentcommentid' => null])
            ->where(['status' => self::STATUS_APPROVED])
            ->orderBy(['createdat' => SORT_DESC]);
    }
}
