<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\Notification;

class NotificationController extends Controller
{
    public $layout = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['list', 'mark-as-read', 'mark-all-as-read', 'count-unread'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        if (!Yii::$app->user->isGuest) {
            $userId = Yii::$app->user->id;
            $notifications = Notification::getUnreadForUser($userId, 20);
            
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'notifications' => array_map(function($notif) {
                    return [
                        'notificationid' => $notif->notificationid,
                        'title' => $notif->title,
                        'content' => $notif->content,
                        'type' => $notif->type,
                        'actionurl' => $notif->actionurl,
                        'isread' => (bool)$notif->isread,
                        'createdat' => $notif->createdat,
                    ];
                }, $notifications),
            ];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => false, 'message' => 'Vui lòng đăng nhập'];
    }

    public function actionMarkAsRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Vui lòng đăng nhập'];
        }

        $id = Yii::$app->request->post('id') ?? Yii::$app->request->get('id');

        if (!$id) {
            return ['success' => false, 'message' => 'Mã thông báo không hợp lệ'];
        }

        $notification = Notification::findOne(['notificationid' => $id, 'userid' => Yii::$app->user->id]);

        if (!$notification) {
            return ['success' => false, 'message' => 'Không tìm thấy thông báo'];
        }

        if ($notification->markAsRead()) {
            return [
                'success' => true,
                'message' => 'Đã đánh dấu là đã đọc',
                'redirectUrl' => $notification->actionurl,
            ];
        }

        return ['success' => false, 'message' => 'Lỗi khi cập nhật thông báo'];
    }

    public function actionMarkAllAsRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Vui lòng đăng nhập'];
        }

        if (Notification::markAllAsReadForUser(Yii::$app->user->id)) {
            return ['success' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc'];
        }

        return ['success' => false, 'message' => 'Lỗi khi cập nhật thông báo'];
    }

    public function actionCountUnread()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['count' => 0];
        }

        $count = Notification::getUnreadCountForUser(Yii::$app->user->id);
        return ['count' => $count];
    }
}
