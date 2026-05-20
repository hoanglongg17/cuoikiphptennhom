<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class EmailNotification extends ActiveRecord
{
    public static function tableName()
    {
        return 'email_notifications';
    }

    const TYPE_COMMENT_ON_POST = 'comment_on_post';
    const TYPE_REPLY_ON_COMMENT = 'reply_on_comment';
    const TYPE_POST_PUBLISHED = 'post_published';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function rules()
    {
        return [
            [['userid', 'type', 'subject'], 'required'],
            [['userid', 'relatedpostid', 'relatedcommentid', 'sendattempts'], 'integer'],
            [['type'], 'in', 'range' => [
                self::TYPE_COMMENT_ON_POST,
                self::TYPE_REPLY_ON_COMMENT,
                self::TYPE_POST_PUBLISHED,
            ]],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_SENT,
                self::STATUS_FAILED,
            ]],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

    public function getPost()
    {
        return $this->hasOne(BlogPost::class, ['postid' => 'relatedpostid']);
    }

    public function getComment()
    {
        return $this->hasOne(BlogNestedComment::class, ['commentid' => 'relatedcommentid']);
    }

    
    public static function createCommentNotification($postid, $userid, $commentid)
    {
        $post = BlogPost::findOne($postid);
        $comment = BlogNestedComment::findOne($commentid);

        if (!$post || !$comment) {
            return false;
        }

        $notification = new static();
        $notification->userid = $post->userid;  
        $notification->type = self::TYPE_COMMENT_ON_POST;
        $notification->relatedpostid = $postid;
        $notification->relatedcommentid = $commentid;
        $notification->subject = "{$comment->user->displayname} đã bình luận trên: {$post->title}";
        $notification->status = self::STATUS_PENDING;

        return $notification->save();
    }

    
    public static function getPendingNotifications($limit = 50)
    {
        return static::find()
            ->where(['status' => self::STATUS_PENDING])
            ->orderBy(['createdat' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    
    public function markAsSent()
    {
        $this->status = self::STATUS_SENT;
        $this->sentat = date('Y-m-d H:i:s');
        return $this->save();
    }

    
    public function markAsFailed()
    {
        $this->status = self::STATUS_FAILED;
        $this->sendattempts++;
        return $this->save();
    }
}
