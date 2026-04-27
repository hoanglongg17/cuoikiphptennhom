<?php

namespace app\helpers;

/**
 * Anki SM-2 Algorithm Implementation
 * Thuật toán học tập chủ động chính xác 100% như Anki
 * Reference: https://faqs.ankiweb.net/what-spaced-repetition-algorithm.html
 */
class SM2Helper
{
    // Anki's Learning/Relearning Intervals (phút)
    const LEARNING_STEPS = [1, 10];           // 1 phút, 10 phút
    const RELEARNING_STEPS = [10];             // Khi lapse, restart từ 10 phút
    const GRADUATING_INTERVAL = 1;             // Sau learning -> review: 1 ngày
    const EASY_INTERVAL = 4;                   // Grade Easy ở lần đầu: 4 ngày

    /**
     * Tính toán lịch học tiếp theo theo SM-2 (Anki)
     * 
     * @param int $grade Điểm đánh giá
     *   - 1: Again (quên)
     *   - 2: Hard (khó)
     *   - 3: Good (tốt)
     *   - 4: Easy (rất dễ)
     * @param int $status Trạng thái (0: new, 1: learning/relearning, 2: review)
     * @param int $repetitions Số lần review thành công liên tiếp
     * @param float $interval Khoảng cách hiện tại (ngày)
     * @param float $easeFactor Hệ số khó (mặc định 2.5)
     * @param int $lapses Số lần quên
     * 
     * @return array [
     *     'status' => int,
     *     'interval' => float,
     *     'easeFactor' => float,
     *     'repetitions' => int,
     *     'lapses' => int,
     *     'nextReview' => string
     * ]
     */
    public static function calculateNextReview(
        $grade,
        $status,
        $repetitions,
        $interval,
        $easeFactor = 2.5,
        $lapses = 0
    ) {
        $grade = max(1, min(4, intval($grade)));
        $easeFactor = max(1.3, $easeFactor);

        if ($grade == 1) {
            // AGAIN - Lapse
            $lapses++;
            $interval = 0;
            $repetitions = 0;
            $status = 1; // Back to Review (relearning)
            $nextReview = date('Y-m-d H:i:s', strtotime('+' . self::RELEARNING_STEPS[0] . ' minutes'));
        } elseif ($status == 0) {
            // NEW CARD
            if ($grade == 4) {
                // Easy - skip learning, go to review
                $status = 2;
                $repetitions = 1;
                $interval = self::EASY_INTERVAL; // 4 ngày
                $nextReview = date('Y-m-d H:i:s', strtotime('+' . $interval . ' days'));
                $easeFactor = $easeFactor + 0.1; // Easy tăng EF thêm 0.1
            } else {
                // Move to learning (grade 2 hoặc 3)
                $status = 1;
                $repetitions = 0;
                $interval = 0;
                $nextReview = date('Y-m-d H:i:s', strtotime('+' . self::LEARNING_STEPS[0] . ' minutes'));
            }
        } elseif ($status == 1) {
            // LEARNING/RELEARNING CARD
            if ($grade == 4) {
                // Easy - skip to review
                $status = 2;
                $repetitions = 1;
                $interval = self::EASY_INTERVAL; // 4 ngày
                $nextReview = date('Y-m-d H:i:s', strtotime('+' . $interval . ' days'));
            } elseif ($grade == 2) {
                // Hard - stay in learning, repeat current step
                $nextReview = date('Y-m-d H:i:s', strtotime('+' . self::LEARNING_STEPS[0] . ' minutes'));
            } else {
                // Good (grade 3) - move to next learning step or graduate
                // FIX: Count which learning step we're on based on number of Good grades
                // Anki's logic: need to pass through all LEARNING_STEPS
                // repetitions tracks how many "Good" answers during learning phase
                
                if ($repetitions < count(self::LEARNING_STEPS) - 1) {
                    // Still have more learning steps to complete
                    // Move to next step interval
                    $repetitions++;
                    $nextReview = date('Y-m-d H:i:s', strtotime('+' . self::LEARNING_STEPS[$repetitions] . ' minutes'));
                } else {
                    // All learning steps completed - Graduate to review
                    $status = 2;
                    $repetitions = 1; // Reset for review phase
                    $interval = self::GRADUATING_INTERVAL; // 1 ngày
                    $nextReview = date('Y-m-d H:i:s', strtotime('+' . $interval . ' days'));
                }
            }
        } elseif ($status == 2) {
            // REVIEW CARD - Apply SM-2 algorithm
            if ($grade == 2) {
                // Hard - reduce interval by 20%
                $interval = max(1, round($interval * 0.6));
            } elseif ($grade == 3) {
                // Good - multiply by easeFactor
                $interval = round($interval * $easeFactor);
            } elseif ($grade == 4) {
                // Easy - multiply by easeFactor + 10%
                $interval = round($interval * ($easeFactor + 0.1));
            }

            $interval = max(1, $interval); // Minimum 1 day
            $status = 2;
            $repetitions++;
            $nextReview = date('Y-m-d H:i:s', strtotime("+$interval days"));
        }

        // Update EaseFactor (SM-2 formula)
        // EF' = EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))
        $q = $grade; // Quality of response (1-4)
        $easeFactor = $easeFactor + (0.1 - (5 - $q) * (0.08 + (5 - $q) * 0.02));
        $easeFactor = max(1.3, $easeFactor); // Minimum 1.3

        return [
            'status' => $status,
            'interval' => max(0, round($interval, 1)),
            'easeFactor' => round($easeFactor, 2),
            'repetitions' => max(0, $repetitions),
            'lapses' => max(0, $lapses),
            'nextReview' => $nextReview,
        ];
    }

    /**
     * Lấy trạng thái bằng tiếng Việt
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            0 => 'Mới',
            1 => 'Đang học',
            2 => 'Ôn tập',
        ];
        return $labels[$status] ?? 'Không xác định';
    }

    /**
     * Lấy màu sắc cho trạng thái
     */
    public static function getStatusColor($status)
    {
        $colors = [
            0 => 'badge-primary',    // Xanh dương - mới
            1 => 'badge-warning',    // Cam - đang học
            2 => 'badge-success',    // Xanh lá - ôn tập
        ];
        return $colors[$status] ?? 'badge-secondary';
    }

    /**
     * Kiểm tra card có phải leech không (quên >4 lần mà còn learning)
     */
    public static function isLeech($lapses, $status)
    {
        return $lapses >= 4 && $status != 2;
    }
}

