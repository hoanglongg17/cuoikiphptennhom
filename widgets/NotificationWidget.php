<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use app\models\Notification;

class NotificationWidget extends Widget
{
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $unreadCount = Notification::getUnreadCountForUser(Yii::$app->user->id);
        return $this->render('notification', [
            'unreadCount' => $unreadCount,
        ]);
    }
}
