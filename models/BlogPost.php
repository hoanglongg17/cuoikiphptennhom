<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class BlogPost extends ActiveRecord
{
    
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_DENIED = 'denied';

    public static function tableName()
    {
        return 'blogposts';
    }

    
    public function rules()
    {
        return [
            [['userid', 'title', 'content'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['slug'], 'string', 'max' => 255],
            [['content', 'excerpt', 'rejectionreason'], 'string'],
            [['status'], 'in', 'range' => ['draft', 'pending', 'published', 'archived', 'denied']],
            [['sharedeckid', 'views'], 'integer'],
            [['is_pinned'], 'boolean'],
            [['publishedat', 'createdat', 'updatedat'], 'safe'],
            [['slug'], 'unique'],
        ];
    }

    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert || !$this->slug) {
                $this->slug = $this->generateSlug($this->title);
            }
            return true;
        }
        return false;
    }

    
    private function generateSlug($title)
    {
        
        $slug = strtolower($title);
        
        
        $slug = $this->removeDiacritics($slug);
        
        
        $slug = preg_replace('/[\s]+/', '-', trim($slug));
        
        
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        
        
        $slug = preg_replace('/-+/', '-', $slug);
        
        return $slug;
    }

    
    private function removeDiacritics($string)
    {
        $characters = array(
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        );
        
        return strtr($string, $characters);
    }

    
    public function attributeLabels()
    {
        return [
            'postid' => 'ID Bài Viết',
            'userid' => 'ID Người Dùng',
            'title' => 'Tiêu Đề',
            'slug' => 'Slug (URL)',
            'content' => 'Nội Dung',
            'excerpt' => 'Tóm Tắt',
            'status' => 'Trạng Thái',
            'rejectionreason' => 'Lý Do Từ Chối',
            'views' => 'Lượt Xem',
            'sharedeckid' => 'Bộ Thẻ Chia Sẻ',
            'publishedat' => 'Ngày Đăng',
            'createdat' => 'Ngày Tạo',
            'updatedat' => 'Cập Nhật Lần Cuối',
        ];
    }

    
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

    
    public function getSharedDeck()
    {
        return $this->hasOne(Deck::class, ['deckid' => 'sharedeckid']);
    }

    
    public function getComments()
    {
        return $this->hasMany(BlogComment::class, ['postid' => 'postid']);
    }

    
    public function getApprovedComments()
    {
        return $this->hasMany(BlogComment::class, ['postid' => 'postid'])
            ->where(['status' => 'approved']);
    }

    
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED && !is_null($this->publishedat);
    }

    
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    
    public function isDenied()
    {
        return $this->status === self::STATUS_DENIED;
    }

    
    public function canBeEditedByOwner()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_DENIED], true);
    }

    
    public function getRejectionReason()
    {
        return trim($this->rejectionreason ?? '');
    }

    
    public function increaseViews()
    {
        $this->views = $this->views + 1;
        return $this->save(false);
    }

    
    public static function findPublished()
    {
        return static::find()
            ->where(['status' => self::STATUS_PUBLISHED])
            ->orderBy(['publishedat' => SORT_DESC]);
    }

    
    public static function findPinned()
    {
        return static::find()
            ->where(['status' => self::STATUS_PUBLISHED, 'is_pinned' => 1])
            ->orderBy(['publishedat' => SORT_DESC]);
    }
    
    
    public static function findFeatured($limit = 5)
    {
        return static::find()
            ->leftJoin(BlogRating::tableName(), 'blogratings.postid = blogposts.postid')
            ->where(['blogposts.status' => self::STATUS_PUBLISHED])
            ->groupBy(['blogposts.postid'])
            ->select(['blogposts.*', 'COUNT(blogratings.postid) as like_count'])
            ->orderBy(['like_count' => SORT_DESC, 'blogposts.publishedat' => SORT_DESC])
            ->limit($limit);
    }

    
    public static function findBySlug($slug)
    {
        return static::findOne(['slug' => $slug]);
    }

    
    public function getCategory()
    {
        return $this->hasOne(BlogCategory::class, ['categoryid' => 'categoryid']);
    }

    
    public function getTags()
    {
        return $this->hasMany(BlogTag::class, ['tagid' => 'tagid'])
            ->via('post_tags');
    }

    
    public function getRatings()
    {
        return $this->hasMany(BlogRating::class, ['postid' => 'postid']);
    }

    
    public function getNestedComments()
    {
        return $this->hasMany(BlogNestedComment::class, ['postid' => 'postid']);
    }

    
    public function getApprovedNestedComments()
    {
        return $this->hasMany(BlogNestedComment::class, ['postid' => 'postid'])
            ->where(['status' => BlogNestedComment::STATUS_APPROVED]);
    }

    
    public function getTopLevelComments()
    {
        return $this->hasMany(BlogNestedComment::class, ['postid' => 'postid'])
            ->where(['status' => BlogNestedComment::STATUS_APPROVED, 'parentcommentid' => null])
            ->orderBy(['createdat' => SORT_DESC]);
    }

    
    public function getLikeCount()
    {
        return BlogRating::getLikeCount($this->postid);
    }

    
    public function getAverageRating()
    {
        return BlogRating::getAverageRating($this->postid);
    }

    
    public function isLikedByUser($userid)
    {
        return BlogRating::isLikedByUser($this->postid, $userid);
    }

    
    public function addTag($tagname)
    {
        $tag = BlogTag::findOrCreate($tagname);
        $postTag = new PostTag();
        $postTag->postid = $this->postid;
        $postTag->tagid = $tag->tagid;
        return $postTag->save();
    }

    
    public static function findByCategory($categoryid)
    {
        return static::find()
            ->where(['categoryid' => $categoryid, 'status' => self::STATUS_PUBLISHED])
            ->orderBy(['publishedat' => SORT_DESC]);
    }

    
    public static function findByTag($tagid)
    {
        return static::find()
            ->innerJoinWith('tags')
            ->where(['blogtags.tagid' => $tagid, 'blogposts.status' => self::STATUS_PUBLISHED])
            ->distinct()
            ->orderBy(['blogposts.publishedat' => SORT_DESC]);
    }

    
    public static function search($keyword)
    {
        return static::find()
            ->where(['status' => self::STATUS_PUBLISHED])
            ->andWhere([
                'or',
                ['like', 'title', $keyword],
                ['like', 'content', $keyword],
                ['like', 'excerpt', $keyword],
            ])
            ->orderBy(['publishedat' => SORT_DESC]);
    }
}

