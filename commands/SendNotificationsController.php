<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\components\EmailNotificationService;

/**
 * SendNotificationsController
 * Gửi email thông báo chờ xử lý
 * 
 * Usage:
 *   php yii send-notifications
 *   php yii send-notifications --batch-size=100 --max-retries=5
 */
class SendNotificationsController extends Controller
{
    /**
     * @var int Số lượng thông báo gửi mỗi lần chạy
     */
    public $batchSize = 50;

    /**
     * @var int Số lần retry tối đa
     */
    public $maxRetries = 3;

    public function options($actionID)
    {
        return ['batchSize', 'maxRetries'];
    }

    /**
     * Gửi tất cả email thông báo chờ xử lý
     */
    public function actionIndex()
    {
        $this->stdout("🚀 Bắt đầu gửi email thông báo...\n");
        
        $result = EmailNotificationService::sendPendingNotifications(
            $this->batchSize,
            $this->maxRetries
        );

        $this->stdout("\n📊 Kết quả:\n");
        $this->stdout("  • Tổng cộng: {$result['total']}\n");
        $this->stdout("  • Gửi thành công: {$result['sent']}\n");
        $this->stdout("  • Gửi thất bại: {$result['failed']}\n");

        // Display statistics
        $stats = EmailNotificationService::getStats();
        $this->stdout("\n📈 Thống kê tổng:\n");
        $this->stdout("  • Chờ xử lý: {$stats['pending']}\n");
        $this->stdout("  • Đã gửi: {$stats['sent']}\n");
        $this->stdout("  • Thất bại: {$stats['failed']}\n");
        $this->stdout("  • Tổng cộng: {$stats['total']}\n");

        if ($result['total'] === 0) {
            $this->stdout("\n✅ Không có thông báo nào chờ xử lý\n");
        } elseif ($result['failed'] === 0) {
            $this->stdout("\n✅ Tất cả thông báo đã được gửi thành công!\n");
        } else {
            $this->stdout("\n⚠️  Có {$result['failed']} thông báo gửi thất bại. Sẽ thử lại vào lần chạy tiếp theo.\n");
        }

        return 0;
    }

    /**
     * Xóa tất cả thông báo cũ (tuỳ chọn)
     * Usage: php yii send-notifications/cleanup --days=30
     */
    public function actionCleanup($days = 30)
    {
        $this->stdout("🗑️  Xóa thông báo cũ hơn $days ngày...\n");

        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $deleted = Yii::$app->db->createCommand()
            ->delete('email_notifications', ['<', 'sentat', $date])
            ->execute();

        $this->stdout("✅ Đã xóa $deleted bản ghi\n");

        return 0;
    }

    /**
     * Hiển thị thống kê chi tiết
     * Usage: php yii send-notifications/stats
     */
    public function actionStats()
    {
        $stats = EmailNotificationService::getStats();

        $this->stdout("\n📊 Thống kê Email Thông báo\n");
        $this->stdout("════════════════════════════════════\n");
        
        $this->stdout("\nStatus:\n");
        $this->stdout("  • Chờ xử lý (Pending): {$stats['pending']}\n");
        $this->stdout("  • Đã gửi (Sent): {$stats['sent']}\n");
        $this->stdout("  • Thất bại (Failed): {$stats['failed']}\n");
        $this->stdout("  • Tổng cộng: {$stats['total']}\n");

        // Calculate success rate
        if ($stats['total'] > 0) {
            $successRate = ($stats['sent'] / $stats['total']) * 100;
            $this->stdout("\nTỷ lệ thành công: " . number_format($successRate, 2) . "%\n");
        }

        $this->stdout("\n════════════════════════════════════\n\n");

        return 0;
    }
}
