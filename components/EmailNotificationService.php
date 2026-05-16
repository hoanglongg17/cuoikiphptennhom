<?php

namespace app\components;

use Yii;
use app\models\EmailNotification;
use app\models\BlogPost;
use app\models\BlogNestedComment;
use yii\helpers\Url;

/**
 * Email Notification Service
 * Manages sending of email notifications for blog activities
 */
class EmailNotificationService
{
    const TYPE_COMMENT_ON_POST = 'comment_on_post';
    const TYPE_REPLY_ON_COMMENT = 'reply_on_comment';
    const TYPE_POST_PUBLISHED = 'post_published';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * Create notification for new comment on post
     */
    public static function notifyCommentOnPost($postId, $commentId)
    {
        $post = BlogPost::findOne($postId);
        if (!$post) {
            return false;
        }

        $comment = BlogNestedComment::findOne($commentId);
        if (!$comment || $comment->status !== 'approved') {
            return false;
        }

        // Notify post author only (not comment author themselves)
        if ($post->userid != $comment->userid) {
            $subject = "Bình luận mới trên bài viết: {$post->title}";
            
            return self::createNotification(
                $post->userid,
                self::TYPE_COMMENT_ON_POST,
                $postId,
                $commentId,
                $subject
            );
        }

        return true;
    }

    /**
     * Create notification for reply to comment
     */
    public static function notifyReplyOnComment($postId, $commentId, $parentCommentId)
    {
        $comment = BlogNestedComment::findOne($commentId);
        $parentComment = BlogNestedComment::findOne($parentCommentId);

        if (!$comment || !$parentComment || $comment->status !== 'approved') {
            return false;
        }

        // Notify parent comment author only (not reply author themselves)
        if ($parentComment->userid != $comment->userid) {
            $subject = "Có bình luận trả lời của bạn";
            
            return self::createNotification(
                $parentComment->userid,
                self::TYPE_REPLY_ON_COMMENT,
                $postId,
                $commentId,
                $subject
            );
        }

        return true;
    }

    /**
     * Create notification for new published post
     */
    public static function notifyPostPublished($postId)
    {
        $post = BlogPost::findOne($postId);
        if (!$post || $post->status !== 'published') {
            return false;
        }

        // In the future, this could notify subscribers
        // For now, we'll skip this to avoid spam

        return true;
    }

    /**
     * Create a notification record
     */
    public static function createNotification($userId, $type, $postId, $relatedCommentId = null, $subject = '')
    {
        $notification = new EmailNotification();
        $notification->userid = $userId;
        $notification->type = $type;
        $notification->relatedpostid = $postId;
        $notification->relatedcommentid = $relatedCommentId;
        $notification->subject = $subject;
        $notification->status = self::STATUS_PENDING;
        $notification->sendattempts = 0;
        $notification->createdat = date('Y-m-d H:i:s');

        return $notification->save();
    }

    /**
     * Send all pending notifications
     * Should be called by a cron job or queue worker
     */
    public static function sendPendingNotifications($batchSize = 50, $maxRetries = 3)
    {
        $notifications = EmailNotification::find()
            ->where(['status' => self::STATUS_PENDING])
            ->andWhere(['<', 'sendattempts', $maxRetries])
            ->orderBy(['createdat' => SORT_ASC])
            ->limit($batchSize)
            ->all();

        $sent = 0;
        foreach ($notifications as $notification) {
            if (self::sendNotification($notification)) {
                $sent++;
            }
        }

        return [
            'total' => count($notifications),
            'sent' => $sent,
            'failed' => count($notifications) - $sent,
        ];
    }

    /**
     * Send a single notification
     */
    public static function sendNotification(EmailNotification $notification)
    {
        try {
            $user = $notification->user;
            if (!$user || !$user->email) {
                $notification->status = self::STATUS_FAILED;
                $notification->save();
                return false;
            }

            // Build email content based on notification type
            $emailData = self::buildEmailContent($notification);

            // Send email
            $result = Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setSubject($emailData['subject'])
                ->setHtmlBody($emailData['html'])
                ->setTextBody($emailData['text'])
                ->send();

            if ($result) {
                $notification->status = self::STATUS_SENT;
                $notification->sentat = date('Y-m-d H:i:s');
                $notification->sendattempts++;
                $notification->save();
                return true;
            } else {
                $notification->sendattempts++;
                $notification->save();
                return false;
            }
        } catch (\Exception $e) {
            Yii::error('Email notification error: ' . $e->getMessage(), __METHOD__);
            
            $notification->sendattempts++;
            if ($notification->sendattempts >= 3) {
                $notification->status = self::STATUS_FAILED;
            }
            $notification->save();
            return false;
        }
    }

    /**
     * Build email content based on notification type
     */
    private static function buildEmailContent(EmailNotification $notification)
    {
        $post = BlogPost::findOne($notification->relatedpostid);
        $user = $notification->user;
        
        $subject = $notification->subject;
        
        switch ($notification->type) {
            case self::TYPE_COMMENT_ON_POST:
                return self::buildCommentNotificationEmail($post, $user, $notification);
                
            case self::TYPE_REPLY_ON_COMMENT:
                return self::buildReplyNotificationEmail($post, $user, $notification);
                
            default:
                return [
                    'subject' => $subject,
                    'html' => '<p>Thông báo: ' . htmlspecialchars($subject) . '</p>',
                    'text' => 'Thông báo: ' . $subject,
                ];
        }
    }

    /**
     * Build comment notification email
     */
    private static function buildCommentNotificationEmail($post, $user, $notification)
    {
        $comment = BlogNestedComment::findOne($notification->relatedcommentid);
        if (!$comment) {
            return ['subject' => 'Thông báo', 'html' => '', 'text' => ''];
        }

        $postUrl = Yii::$app->urlManager->createAbsoluteUrl([
            'blog/view',
            'slug' => $post->slug,
        ]);

        $html = <<<HTML
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2>Bình luận mới trên bài viết của bạn</h2>
    
    <p>Xin chào <strong>{$user->displayname}</strong>,</p>
    
    <p>Có bình luận mới trên bài viết "<strong>{$post->title}</strong>":</p>
    
    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Từ: {$comment->user->displayname}</strong></p>
        <p style="color: #666; font-size: 0.9em;">{$comment->content}</p>
    </div>
    
    <p>
        <a href="{$postUrl}" style="background: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Xem bài viết
        </a>
    </p>
    
    <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
    
    <p style="color: #999; font-size: 0.85em;">
        Đây là thông báo tự động từ Andi Flashcard Master. Vui lòng không trả lời email này.
    </p>
</div>
HTML;

        $text = <<<TEXT
Bình luận mới trên bài viết của bạn

Xin chào {$user->displayname},

Có bình luận mới trên bài viết "{$post->title}":

Từ: {$comment->user->displayname}
{$comment->content}

Xem bài viết: {$postUrl}

---
Đây là thông báo tự động từ Andi Flashcard Master.
TEXT;

        return [
            'subject' => $notification->subject,
            'html' => $html,
            'text' => $text,
        ];
    }

    /**
     * Build reply notification email
     */
    private static function buildReplyNotificationEmail($post, $user, $notification)
    {
        $comment = BlogNestedComment::findOne($notification->relatedcommentid);
        if (!$comment) {
            return ['subject' => 'Thông báo', 'html' => '', 'text' => ''];
        }

        $postUrl = Yii::$app->urlManager->createAbsoluteUrl([
            'blog/view',
            'slug' => $post->slug,
        ]);

        $html = <<<HTML
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2>Có bình luận trả lời bình luận của bạn</h2>
    
    <p>Xin chào <strong>{$user->displayname}</strong>,</p>
    
    <p><strong>{$comment->user->displayname}</strong> đã trả lời bình luận của bạn trên bài viết "<strong>{$post->title}</strong>":</p>
    
    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Trả lời từ: {$comment->user->displayname}</strong></p>
        <p style="color: #666; font-size: 0.9em;">{$comment->content}</p>
    </div>
    
    <p>
        <a href="{$postUrl}" style="background: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Xem bài viết
        </a>
    </p>
    
    <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
    
    <p style="color: #999; font-size: 0.85em;">
        Đây là thông báo tự động từ Andi Flashcard Master. Vui lòng không trả lời email này.
    </p>
</div>
HTML;

        $text = <<<TEXT
Có bình luận trả lời bình luận của bạn

Xin chào {$user->displayname},

{$comment->user->displayname} đã trả lời bình luận của bạn trên bài viết "{$post->title}":

Từ: {$comment->user->displayname}
{$comment->content}

Xem bài viết: {$postUrl}

---
Đây là thông báo tự động từ Andi Flashcard Master.
TEXT;

        return [
            'subject' => $notification->subject,
            'html' => $html,
            'text' => $text,
        ];
    }

    /**
     * Get notification statistics
     */
    public static function getStats()
    {
        return [
            'pending' => EmailNotification::find()->where(['status' => self::STATUS_PENDING])->count(),
            'sent' => EmailNotification::find()->where(['status' => self::STATUS_SENT])->count(),
            'failed' => EmailNotification::find()->where(['status' => self::STATUS_FAILED])->count(),
            'total' => EmailNotification::find()->count(),
        ];
    }
}
