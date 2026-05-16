<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * BlogTag Model - Nhãn cho bài viết
 */
class BlogTag extends ActiveRecord
{
    public static function tableName()
    {
        return 'blogtags';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 50],
            [['name', 'slug'], 'unique'],
            [['usagecount'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tagid' => 'Tag ID',
            'name' => 'Tên Tag',
            'slug' => 'Slug',
            'usagecount' => 'Số Lần Sử Dụng',
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(BlogPost::class, ['tagid' => 'tagid'])
            ->via('post_tags');
    }

    public function getPost_tags()
    {
        return $this->hasMany(PostTag::class, ['tagid' => 'tagid']);
    }

    public static function findBySlug($slug)
    {
        return static::findOne(['slug' => $slug]);
    }

    public static function findOrCreate($name)
    {
        $slug = self::slugify($name);
        $tag = static::findOne(['slug' => $slug]);

        if ($tag === null) {
            $tag = new static();
            $tag->name = $name;
            $tag->slug = $slug;
            $tag->save();
        }

        return $tag;
    }

    private static function slugify($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[\s]+/', '-', trim($text));
        $text = preg_replace('/[^a-z0-9\-]/', '', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
