<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class BlogRating extends ActiveRecord
{
    public static function tableName()
    {
        return 'blogratings';
    }

    public function rules()
    {
        return [
            [['postid', 'userid', 'rating'], 'required'],
            [['postid', 'userid'], 'integer'],
            [['rating'], 'integer', 'min' => 1, 'max' => 5],
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

    
    public static function isLikedByUser($postid, $userid)
    {
        return static::findOne(['postid' => $postid, 'userid' => $userid]) !== null;
    }

    
    public static function getLikeCount($postid)
    {
        return static::find()
            ->where(['postid' => $postid])
            ->count();
    }

    
    public static function getAverageRating($postid)
    {
        $avg = static::find()
            ->where(['postid' => $postid])
            ->average('rating');

        return $avg ? round($avg, 1) : 0;
    }
}
