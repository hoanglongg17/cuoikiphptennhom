<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\ReviewLog;


class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'users';
    }

    
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    
    public static function findByGoogleId($googleId)
    {
        return static::findOne(['googleid' => $googleId]);
    }

    
    public function getId()
    {
        return $this->userid;
    }

    
    public function getAuthKey()
    {
        return 'auth_key_' . $this->userid;
    }

    
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function setPassword($password)
    {
        $this->passwordhash = Yii::$app->security->generatePasswordHash($password);
    }
    
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    
    public function validatePassword($password)
    {
        
        if (empty($this->passwordhash)) {
            return false;
        }
        
        
        try {
            return Yii::$app->security->validatePassword($password, $this->passwordhash);
        } catch (\Exception $e) {
            
            return $this->passwordhash === $password;
        }
    }
    
    
    public function getBlogPosts()
    {
        return $this->hasMany(BlogPost::class, ['userid' => 'userid']);
    }

    
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