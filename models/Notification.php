<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Notification extends ActiveRecord
{
    const TYPE_APPROVED = 'approved';
    const TYPE_REJECTED = 'rejected';
    const TYPE_PENDING = 'pending';

    public static function tableName()
    {
        return 'notifications';
    }

    public function rules()
    {
        return [
            [['userid', 'type', 'title', 'content'], 'required'],
            [['userid', 'postid'], 'integer'],
            [['type'], 'in', 'range' => ['approved', 'rejected', 'pending']],
            [['title'], 'string', 'max' => 255],
            [['content', 'actionurl'], 'string'],
            [['isread'], 'boolean'],
            [['createdat', 'readedat'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'notificationid' => 'Mã thông báo',
            'userid' => 'Mã người dùng',
            'postid' => 'Mã bài viết',
            'type' => 'Loại thông báo',
            'title' => 'Tiêu đề',
            'content' => 'Nội dung',
            'actionurl' => 'Đường dẫn',
            'isread' => 'Đã đọc',
            'createdat' => 'Ngày tạo',
            'readedat' => 'Ngày đọc',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

    public function getPost()
    {
        return $this->hasOne(BlogPost::class, ['postid' => 'postid']);
    }

    public static function createApprovedNotification($userId, $postId, $postTitle)
    {
        $notification = new self();
        $notification->userid = $userId;
        $notification->postid = $postId;
        $notification->type = self::TYPE_APPROVED;
        $notification->title = 'Bài viết đã được duyệt';
        $notification->content = 'Bài viết "' . $postTitle . '" của bạn đã được admin duyệt và xuất bản.';
        $notification->actionurl = Yii::$app->urlManager->createAbsoluteUrl(['blog/my-posts']);
        $notification->isread = false;
        return $notification->save();
    }

    public static function createRejectedNotification($userId, $postId, $postTitle, $reason = '')
    {
        $notification = new self();
        $notification->userid = $userId;
        $notification->postid = $postId;
        $notification->type = self::TYPE_REJECTED;
        $notification->title = 'Bài viết bị từ chối';
        $notification->content = 'Bài viết "' . $postTitle . '" của bạn đã bị từ chối.' . (!empty($reason) ? ' Lý do: ' . $reason : '');
        $notification->actionurl = Yii::$app->urlManager->createAbsoluteUrl(['blog/my-posts']);
        $notification->isread = false;
        return $notification->save();
    }

    public static function createPendingNotification($userId, $postId, $postTitle, $authorName)
    {
        $notification = new self();
        $notification->userid = $userId;
        $notification->postid = $postId;
        $notification->type = self::TYPE_PENDING;
        $notification->title = 'Bài viết chờ duyệt';
        $notification->content = 'Người dùng "' . $authorName . '" đã gửi bài viết "' . $postTitle . '" chờ duyệt.';
        $notification->actionurl = Yii::$app->urlManager->createAbsoluteUrl(['admin/blog-list']);
        $notification->isread = false;
        return $notification->save();
    }

    public function markAsRead()
    {
        if (!$this->isread) {
            $this->isread = true;
            $this->readedat = date('Y-m-d H:i:s');
            return $this->save();
        }
        return true;
    }

    public static function markAllAsReadForUser($userId)
    {
        return Yii::$app->db->createCommand()
            ->update('notifications', [
                'isread' => true,
                'readedat' => date('Y-m-d H:i:s')
            ], ['userid' => $userId, 'isread' => false])
            ->execute();
    }

    public static function getUnreadCountForUser($userId)
    {
        return self::find()
            ->where(['userid' => $userId, 'isread' => false])
            ->count();
    }

    public static function getUnreadForUser($userId, $limit = 10)
    {
        return self::find()
            ->where(['userid' => $userId])
            ->orderBy(['createdat' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
