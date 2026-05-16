<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\BlogPost;
use app\models\BlogComment;
use app\models\BlogCategory;
use app\models\BlogTag;
use app\models\BlogRating;
use app\models\BlogNestedComment;
use app\models\EmailNotification;
use app\components\EmailNotificationService;
use yii\data\Pagination;

/**
 * BlogController - Quản lý Blog công khai
 * Cho phép người dùng xem bài viết, bình luận, và chia sẻ decks
 */
class BlogController extends Controller
{
    public $layout = 'main';
    /**
     * Quy tắc kiểm soát truy cập
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'],  // Public và logged-in users
                    ],
                    [
                        'actions' => ['create', 'edit', 'delete', 'my-posts', 'add-comment'],
                        'allow' => true,
                        'roles' => ['@'],  // Chỉ logged-in users
                    ],
                ],
            ],
        ];
    }

    /**
     * Hiển thị danh sách bài viết blog (public)
     */
    public function actionIndex()
    {
        $query = BlogPost::findPublished();

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 10,
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'posts' => $posts,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Tìm kiếm bài viết blog
     */
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

    /**
     * Xem chi tiết một bài viết blog
     */
    public function actionView($slug)
    {
        $post = BlogPost::findBySlug($slug);

        if ($post === null || !$post->isPublished()) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        // Tăng lượt xem
        $post->increaseViews();

        // Lấy bình luận được duyệt
        $comments = $post->getApprovedComments()->all();

        // Tạo model bình luận mới
        $commentModel = new BlogComment();
        $commentModel->postid = $post->postid;

        // Xử lý bình luận mới
        if ($commentModel->load(Yii::$app->request->post())) {
            $commentModel->userid = Yii::$app->user->id;
            $commentModel->status = BlogComment::STATUS_PENDING;  // Chờ duyệt

            if ($commentModel->save()) {
                Yii::$app->session->setFlash('success', 'Bình luận của bạn đã được gửi và chờ duyệt!');
                return $this->redirect(['view', 'slug' => $slug]);
            }
        }

        return $this->render('view', [
            'post' => $post,
            'comments' => $comments,
            'commentModel' => $commentModel,
        ]);
    }

    /**
     * Tạo bài viết blog mới (cho users)
     */
    public function actionCreate()
    {
        $model = new BlogPost();
        $model->userid = Yii::$app->user->id;
        $model->status = BlogPost::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Kiểm tra xem user có phải admin (admin có thể publish ngay, user phải chờ duyệt)
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            if (!$isAdmin) {
                $model->status = BlogPost::STATUS_DRAFT;  // Users tạo bài viết nháp
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết được tạo thành công! (Admin sẽ duyệt)');
                return $this->redirect(['my-posts']);
            }
        }

        return $this->render('form', [
            'model' => $model,
            'isNew' => true,
        ]);
    }

    /**
     * Chỉnh sửa bài viết (chỉ owner hoặc admin)
     */
    public function actionEdit($id)
    {
        $model = $this->findBlogPost($id);

        // Kiểm tra quyền
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        if ($model->userid !== Yii::$app->user->id && !$isAdmin) {
            throw new NotFoundHttpException('Bạn không có quyền sửa bài viết này.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Bài viết được cập nhật thành công!');
            return $this->redirect(['my-posts']);
        }

        return $this->render('form', [
            'model' => $model,
            'isNew' => false,
        ]);
    }

    /**
     * Xóa bài viết (chỉ owner hoặc admin)
     */
    public function actionDelete($id)
    {
        $model = $this->findBlogPost($id);

        // Kiểm tra quyền
        /** @var \app\models\User $user */
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

    /**
     * Xem bài viết của tôi
     */
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



    /**
     * Tìm model BlogPost
     */
    protected function findBlogPost($id)
    {
        $model = BlogPost::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        return $model;
    }

    /**
     * Like bài viết (AJAX)
     */
    public function actionLike($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->asJson(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        }

        $post = $this->findBlogPost($id);
        $userid = Yii::$app->user->id;

        // Kiểm tra xem đã like chưa
        $existing = BlogRating::findOne(['postid' => $id, 'userid' => $userid]);

        if ($existing) {
            // Bỏ like
            $existing->delete();
            $liked = false;
        } else {
            // Thêm like
            $rating = new BlogRating();
            $rating->postid = $id;
            $rating->userid = $userid;
            $rating->rating = 1;
            $rating->save();
            $liked = true;
        }

        return $this->asJson([
            'success' => true,
            'liked' => $liked,
            'likeCount' => BlogRating::getLikeCount($id),
        ]);
    }

    /**
     * Thêm bình luận lồng (AJAX hoặc POST)
     */
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
        $comment->status = BlogNestedComment::STATUS_PENDING;

        if ($comment->load(Yii::$app->request->post()) && $comment->save()) {
            // Send email notification based on comment type
            if ($parentcommentid) {
                // This is a reply to another comment
                EmailNotificationService::notifyReplyOnComment($postid, $comment->commentid, $parentcommentid);
            } else {
                // This is a top-level comment on the post
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

    /**
     * Lấy bình luận theo category
     */
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

    /**
     * Lấy bài viết theo tag
     */
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
