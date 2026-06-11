<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\BlogPost;
use app\models\BlogComment;
use app\models\BlogCategory;
use app\models\BlogTag;
use app\models\BlogRating;
use app\models\BlogNestedComment;
use app\models\EmailNotification;
use app\models\Notification;
use app\models\User;
use app\components\EmailNotificationService;
use yii\data\Pagination;


class BlogController extends Controller
{
    public $layout = 'main';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'],  
                    ],
                    [
                        'actions' => ['create', 'edit', 'delete', 'my-posts', 'add-comment', 'delete-comment', 'like'],
                        'allow' => true,
                        'roles' => ['@'],  
                    ],
                ],
            ],
        ];
    }

    
    public function actionIndex()
    {
        $keyword = Yii::$app->request->get('q', '');
        
        $pinnedPosts = [];
        $pinnedPagination = null;

        if (!empty($keyword)) {
            $query = BlogPost::search($keyword);
            $featuredPosts = [];
        } else {
            $query = BlogPost::findPublished();
            
            $pinnedQuery = BlogPost::findPinned();
            $pinnedPagination = new Pagination([
                'totalCount' => $pinnedQuery->count(),
                'pageSize' => 3,
                'pageParam' => 'pinned-page',
            ]);
            $pinnedPosts = $pinnedQuery
                ->offset($pinnedPagination->offset)
                ->limit($pinnedPagination->limit)
                ->all();

            
            $featuredPosts = BlogPost::findFeatured(5)->all();
        }

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 10,
            'pageParam' => 'page',
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'posts' => $posts,
            'pagination' => $pagination,
            'keyword' => $keyword,
            'featuredPosts' => $featuredPosts,
            'pinnedPosts' => $pinnedPosts,
            'pinnedPagination' => $pinnedPagination,
        ]);
    }

    
    public function actionSearch()
    {
        $keyword = Yii::$app->request->get('q', '');
        $posts = [];
        $pagination = null;

        if (!empty($keyword)) {
            $query = BlogPost::search($keyword);
            
            $countQuery = clone $query;
            $pagination = new Pagination([
                'totalCount' => $countQuery->count(),
                'pageSize' => 10,
            ]);

            $posts = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
        }

        return $this->render('search', [
            'posts' => $posts,
            'pagination' => $pagination,
            'keyword' => $keyword,
        ]);
    }

    
    public function actionView($slug)
    {
        $post = BlogPost::findBySlug($slug);

        if ($post === null || !$post->isPublished()) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        
        $post->increaseViews();

        
        $comments = $post->getApprovedComments()->all();

        
        $commentModel = new BlogComment();
        $commentModel->postid = $post->postid;

        
        if ($commentModel->load(Yii::$app->request->post())) {
            $commentModel->userid = Yii::$app->user->id;
            $commentModel->status = BlogComment::STATUS_APPROVED;  

            if ($commentModel->save()) {
                Yii::$app->session->setFlash('success', 'Bình luận của bạn đã được đăng!');
                return $this->redirect(['view', 'slug' => $slug]);
            }
        }

        return $this->render('view', [
            'post' => $post,
            'comments' => $comments,
            'commentModel' => $commentModel,
        ]);
    }

    
    public function actionCreate()
    {
        $model = new BlogPost();
        $model->userid = Yii::$app->user->id;
        $model->status = BlogPost::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            
            $user = Yii::$app->user->identity;
            $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            
            if (!$isAdmin) {
                if ($model->status === BlogPost::STATUS_PUBLISHED) {
                    $model->status = BlogPost::STATUS_PENDING;
                } else {
                    $model->status = BlogPost::STATUS_DRAFT;
                }
                $model->publishedat = null;
                $model->rejectionreason = null;
            } elseif ($model->status === BlogPost::STATUS_PUBLISHED && is_null($model->publishedat)) {
                
                $model->publishedat = date('Y-m-d H:i:s');
            }

            if ($model->save()) {
                if ($model->status === BlogPost::STATUS_PENDING) {
                    $admins = User::find()->where(['role' => 'admin'])->all();
                    foreach ($admins as $admin) {
                        Notification::createPendingNotification($admin->userid, $model->postid, $model->title, $user->displayname);
                    }
                }

                $message = $isAdmin 
                    ? ($model->status === BlogPost::STATUS_PUBLISHED 
                        ? 'Bài viết được xuất bản thành công!' 
                        : 'Bài viết được tạo thành công!')
                    : ($model->status === BlogPost::STATUS_PENDING 
                        ? 'Bài viết đã được gửi duyệt. Vui lòng chờ admin duyệt.' 
                        : 'Bài viết nháp đã được lưu!');
                Yii::$app->session->setFlash('success', $message);
                return $this->redirect(['my-posts']);
            }
        }

        return $this->render('form', [
            'model' => $model,
            'isNew' => true,
        ]);
    }

    
    public function actionEdit($id)
    {
        $model = $this->findBlogPost($id);

        
        
        $user = Yii::$app->user->identity;
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        if ($model->userid !== Yii::$app->user->id && !$isAdmin) {
            throw new NotFoundHttpException('Bạn không có quyền sửa bài viết này.');
        }

        if (!$isAdmin && in_array($model->status, [BlogPost::STATUS_PUBLISHED, BlogPost::STATUS_ARCHIVED], true)) {
            throw new NotFoundHttpException('Bạn không thể chỉnh sửa bài viết đã được đăng hoặc lưu trữ.');
        }

        $originalStatus = $model->status;

        if ($model->load(Yii::$app->request->post())) {
            if (!$isAdmin) {
                if ($originalStatus === BlogPost::STATUS_PENDING) {
                    
                    $model->status = BlogPost::STATUS_PENDING;
                } elseif ($model->status === BlogPost::STATUS_PUBLISHED) {
                    $model->status = BlogPost::STATUS_PENDING;
                    $model->rejectionreason = null;
                } elseif ($model->status !== BlogPost::STATUS_DRAFT) {
                    $model->status = BlogPost::STATUS_DRAFT;
                }
                $model->publishedat = null;
            } else {
                if ($model->status === BlogPost::STATUS_PUBLISHED && is_null($model->publishedat)) {
                    $model->publishedat = date('Y-m-d H:i:s');
                }
                if ($model->status !== BlogPost::STATUS_DENIED) {
                    $model->rejectionreason = null;
                }
            }
            
            if ($model->save()) {
                if (!$isAdmin && $originalStatus === BlogPost::STATUS_DENIED && $model->status === BlogPost::STATUS_PENDING) {
                    $admins = User::find()->where(['role' => 'admin'])->all();
                    foreach ($admins as $admin) {
                        Notification::createPendingNotification($admin->userid, $model->postid, $model->title, $user->displayname);
                    }
                }

                Yii::$app->session->setFlash('success', 'Bài viết được cập nhật thành công!');
                return $this->redirect(['my-posts']);
            }
        }

        return $this->render('form', [
            'model' => $model,
            'isNew' => false,
        ]);
    }

    
    public function actionDelete($id)
    {
        $model = $this->findBlogPost($id);

        
        
        $user = Yii::$app->user->identity;
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        if ($model->userid !== Yii::$app->user->id && !$isAdmin) {
            throw new NotFoundHttpException('Bạn không có quyền xóa bài viết này.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Bài viết được xóa thành công!');
        }

        return $this->redirect(['my-posts']);
    }

    
    public function actionMyPosts()
    {
        $query = BlogPost::find()
            ->where(['userid' => Yii::$app->user->id])
            ->orderBy(['createdat' => SORT_DESC]);

        $posts = $query->all();

        return $this->render('my-posts', [
            'posts' => $posts,
        ]);
    }



    
    protected function findBlogPost($id)
    {
        $model = BlogPost::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        return $model;
    }

    
    public function actionLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Vui lòng đăng nhập'];
        }

        try {
            $id = Yii::$app->request->get('id');
            if (!$id) {
                return ['success' => false, 'message' => 'ID bài viết không hợp lệ'];
            }
            
            $post = $this->findBlogPost($id);
            $userid = Yii::$app->user->id;

            
            $existing = BlogRating::findOne(['postid' => $id, 'userid' => $userid]);

            if ($existing) {
                
                $existing->delete();
                $liked = false;
            } else {
                
                $rating = new BlogRating();
                $rating->postid = $id;
                $rating->userid = $userid;
                $rating->rating = 1;
                $rating->save();
                $liked = true;
            }

            return [
                'success' => true,
                'liked' => $liked,
                'likeCount' => BlogRating::getLikeCount($id),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }

    
    public function actionAddComment($postid)
    {
        if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                return $this->asJson(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            }
            return $this->redirect(['site/login']);
        }

        $post = $this->findBlogPost($postid);
        $parentcommentid = Yii::$app->request->post('parentcommentid', null);

        $comment = new BlogNestedComment();
        $comment->postid = $postid;
        $comment->userid = Yii::$app->user->id;
        $comment->parentcommentid = $parentcommentid;
        $comment->status = BlogNestedComment::STATUS_APPROVED;

        if ($comment->load(Yii::$app->request->post()) && $comment->save()) {
            
            if ($parentcommentid) {
                
                EmailNotificationService::notifyReplyOnComment($postid, $comment->commentid, $parentcommentid);
            } else {
                
                EmailNotificationService::notifyCommentOnPost($postid, $comment->commentid);
            }

            if (Yii::$app->request->isAjax) {
                return $this->asJson(['success' => true, 'message' => 'Bình luận đã được gửi']);
            }

            Yii::$app->session->setFlash('success', 'Bình luận của bạn đã được gửi!');
            return $this->redirect(['view', 'slug' => $post->slug]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->asJson(['success' => false, 'errors' => $comment->getErrors()]);
        }

        return $this->redirect(['view', 'slug' => $post->slug]);
    }

    
    public function actionDeleteComment($id)
    {
        $user = Yii::$app->user->identity;

        
        $nested = BlogNestedComment::findOne($id);
        if ($nested) {
            if ($nested->userid != Yii::$app->user->id && !($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
                throw new NotFoundHttpException('Bạn không có quyền xóa bình luận này.');
            }
            $post = BlogPost::findOne($nested->postid);
            $nested->delete();
            Yii::$app->session->setFlash('success', 'Bình luận đã được xóa');
            return $this->redirect(['view', 'slug' => $post->slug]);
        }

        
        $comment = BlogComment::findOne($id);
        if ($comment) {
            if ($comment->userid != Yii::$app->user->id && !($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
                throw new NotFoundHttpException('Bạn không có quyền xóa bình luận này.');
            }
            $post = BlogPost::findOne($comment->postid);
            $comment->delete();
            Yii::$app->session->setFlash('success', 'Bình luận đã được xóa');
            return $this->redirect(['view', 'slug' => $post->slug]);
        }

        throw new NotFoundHttpException('Bình luận không tồn tại.');
    }

    
    public function actionCategory($slug)
    {
        $category = BlogCategory::findBySlug($slug);

        if ($category === null) {
            throw new NotFoundHttpException('Danh mục không tồn tại.');
        }

        $query = BlogPost::findByCategory($category->categoryid);

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 10,
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('category', [
            'category' => $category,
            'posts' => $posts,
            'pagination' => $pagination,
        ]);
    }

    
    public function actionTag($slug)
    {
        $tag = BlogTag::findBySlug($slug);

        if ($tag === null) {
            throw new NotFoundHttpException('Tag không tồn tại.');
        }

        $query = BlogPost::findByTag($tag->tagid);

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 10,
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('tag', [
            'tag' => $tag,
            'posts' => $posts,
            'pagination' => $pagination,
        ]);
    }
}
