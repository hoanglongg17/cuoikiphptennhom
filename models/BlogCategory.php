<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * BlogCategory Model - Danh mục bài viết
 */
class BlogCategory extends ActiveRecord
{
    public static function tableName()
    {
        return 'blogcategories';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 100],
            [['description'], 'string'],
            [['color'], 'string', 'max' => 20],
            [['name', 'slug'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'categoryid' => 'Danh Mục ID',
            'name' => 'Tên Danh Mục',
            'slug' => 'Slug',
            'description' => 'Mô Tả',
            'color' => 'Màu Sắc',
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(BlogPost::class, ['categoryid' => 'categoryid']);
    }

    public static function findBySlug($slug)
    {
        return static::findOne(['slug' => $slug]);
    }
}
