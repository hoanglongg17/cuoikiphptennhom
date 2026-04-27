<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\ReviewLog;

/**
 * User model kết nối trực tiếp với bảng users
 * Chế độ: So khớp mật khẩu không mã hóa (Plain Text) và Google Login
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Tìm danh tính dựa trên ID - Dùng để duy trì đăng nhập qua Session
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Không sử dụng Access Token trong phiên bản này
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Tìm người dùng bằng Email - Dùng cho logic LoginForm thường
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Tìm người dùng bằng Google ID - Dùng cho đăng nhập Google
     */
    public static function findByGoogleId($googleId)
    {
        return static::findOne(['googleid' => $googleId]);
    }

    /**
     * Trả về ID người dùng (primary key)
     */
    public function getId()
    {
        return $this->userid;
    }

    /**
     * Trả về Auth Key - Dùng cho chức năng "Ghi nhớ đăng nhập" (Remember Me)
     */
    public function getAuthKey()
    {
        return 'auth_key_' . $this->userid;
    }

    /**
     * Kiểm tra Auth Key khi người dùng quay lại bằng Cookie
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function setPassword($password)
    {
        $this->passwordhash = Yii::$app->security->generatePasswordHash($password);
    }
    /**
     * Kiểm tra mật khẩu KHÔNG MÃ HÓA
     * So sánh trực tiếp mật khẩu nhập vào với cột passwordhash trong DB
     */
    public function validatePassword($password)
    {
        // Nếu passwordhash trống (ví dụ: dùng Google Login), không cho phép đăng nhập bằng pass
        if (empty($this->passwordhash)) {
            return false;
        }
        
        // So khớp mật khẩu thuần với mã hash trong database
        try {
            return Yii::$app->security->validatePassword($password, $this->passwordhash);
        } catch (\Exception $e) {
            // Hỗ trợ tạm thời cho các tài khoản cũ chưa kịp hash (nếu cần)
            return $this->passwordhash === $password;
        }
    }

    /**
     * Lấy chuỗi ngày học liên tiếp hiện tại (chỉ tính khi có ít nhất 1 thẻ học hôm nay)
     * Nếu hôm nay không học, chuỗi sẽ reset.
     */
    public function getCurrentStreak()
    {
        $today = date('Y-m-d');
        $reviewDates = ReviewLog::find()
            ->joinWith('card')
            ->where(['cards.userid' => $this->userid])
            ->select(["DATE(reviewdate) AS review_date"])
            ->distinct()
            ->orderBy(['review_date' => SORT_DESC])
            ->column();

        $streak = 0;
        $expectedDate = $today;

        foreach ($reviewDates as $date) {
            if ($date > $expectedDate) {
                continue;
            }
            if ($date !== $expectedDate) {
                break;
            }
            $streak++;
            $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
        }

        return $streak;
    }

    /**
     * Logic tạo người dùng mới hoặc cập nhật khi đăng nhập Google
     */
    public static function loginWithGoogle($attributes)
    {
        $googleId = (string)$attributes['id'];
        $email = $attributes['email'];
        $name = $attributes['name'];
        $avatar = $attributes['picture'] ?? null;

        $user = self::findByGoogleId($googleId);
        
        if (!$user) {
            $user = self::findByEmail($email);
            
            if ($user) {
                $user->googleid = $googleId;
                if (!$user->avatarurl) $user->avatarurl = $avatar;
                $user->save(false);
            } else {
                $user = new self();
                $user->email = $email;
                $user->googleid = $googleId;
                $user->displayname = $name;
                $user->avatarurl = $avatar;
                $user->passwordhash = null; 
                $user->createdat = date('Y-m-d H:i:s');
                $user->save(false);
            }
        }
        return $user;
    }
}