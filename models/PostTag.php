<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * PostTag Model - Liên kết giữa BlogPost và BlogTag
 */
class PostTag extends ActiveRecord
{
    public static function tableName()
    {
        return 'post_tags';
    }

    public function getPost()
    {
        return $this->hasOne(BlogPost::class, ['postid' => 'postid']);
    }

    public function getTag()
    {
        return $this->hasOne(BlogTag::class, ['tagid' => 'tagid']);
    }
}
