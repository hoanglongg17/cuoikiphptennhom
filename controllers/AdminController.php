<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\BlogPost;
use app\models\BlogComment;
use app\models\BlogNestedComment;
use app\models\User;
use yii\data\Pagination;

/**
 * AdminController - Quản lý Admin Panel
 * Chỉ cho Admin truy cập
 */
class AdminController extends Controller
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
                'only' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-comments'],
                'rules' => [
                    [
                        'actions' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-comments'],
                        'allow' => true,
                        'roles' => ['@'],  // Phải đăng nhập
                        'matchCallback' => function ($rule, $action) {
                            // Kiểm tra xem người dùng có phải là admin không
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'blog-delete' => ['POST', 'DELETE'],
                    'blog-publish' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Admin Dashboard - Trang chủ quản lý
     */
    public function actionDashboard()
    {
        // Lấy thống kê cơ bản
        $totalPosts = BlogPost::find()->count();
        $publishedPosts = BlogPost::find()->where(['status' => BlogPost::STATUS_PUBLISHED])->count();
        $draftPosts = BlogPost::find()->where(['status' => BlogPost::STATUS_DRAFT])->count();
        $totalComments = BlogComment::find()->count();
        $pendingComments = BlogComment::find()->where(['status' => BlogComment::STATUS_PENDING])->count();
        $totalUsers = User::find()->count();

        // Lấy 5 bài viết mới nhất
        $recentPosts = BlogPost::find()
            ->orderBy(['createdat' => SORT_DESC])
            ->limit(5)
            ->all();

        // Lấy bình luận chờ duyệt
        $pendingComments = BlogComment::find()
            ->where(['status' => BlogComment::STATUS_PENDING])
            ->orderBy(['createdat' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'draftPosts' => $draftPosts,
            'totalComments' => $totalComments,
            'pendingCommentsCount' => BlogComment::find()->where(['status' => BlogComment::STATUS_PENDING])->count(),
            'totalUsers' => $totalUsers,
            'recentPosts' => $recentPosts,
            'pendingComments' => $pendingComments,
        ]);
    }

    /**
     * Danh sách các bài viết blog
     */
    public function actionBlogList()
    {
        $status = Yii::$app->request->get('status', '');
        
        $query = BlogPost::find();
        
        if ($status) {
            $query->where(['status' => $status]);
        }

        $posts = $query->orderBy(['createdat' => SORT_DESC])
            ->all();

        return $this->render('blog-list', [
            'posts' => $posts,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Tạo bài viết blog mới
     */
    public function actionBlogCreate()
    {
        $model = new BlogPost();
        $model->userid = Yii::$app->user->id;
        $model->status = BlogPost::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Bài viết được tạo thành công!');
            return $this->redirect(['blog-list']);
        }

        return $this->render('blog-form', [
            'model' => $model,
            'isNew' => true,
        ]);
    }

    /**
     * Chỉnh sửa bài viết blog
     */
    public function actionBlogEdit($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Bài viết được cập nhật thành công!');
            return $this->redirect(['blog-list']);
        }

        return $this->render('blog-form', [
            'model' => $model,
            'isNew' => false,
        ]);
    }

    /**
     * Xuất bản bài viết
     */
    public function actionBlogPublish($id)
    {
        $model = $this->findBlogPost($id);
        
        if ($model->status === BlogPost::STATUS_DRAFT) {
            $model->status = BlogPost::STATUS_PUBLISHED;
            $model->publishedat = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết được xuất bản thành công!');
            }
        }

        return $this->redirect(['blog-list']);
    }

    /**
     * Xóa bài viết blog
     */
    public function actionBlogDelete($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Bài viết được xóa thành công!');
        }

        return $this->redirect(['blog-list']);
    }

    /**
     * Quản lý bình luận blog
     */
    public function actionBlogComments()
    {
        $status = Yii::$app->request->get('status', '');
        
        $query = BlogComment::find();
        
        if ($status) {
            $query->where(['status' => $status]);
        }

        $comments = $query->orderBy(['createdat' => SORT_DESC])
            ->all();

        return $this->render('blog-comments', [
            'comments' => $comments,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Quản lý bình luận blog (nested comments)
     */
    public function actionComments()
    {
        $statusFilter = Yii::$app->request->get('status', '');
        
        $query = BlogNestedComment::find()->with('user', 'post');
        
        if ($statusFilter) {
            $query->where(['status' => $statusFilter]);
        }

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 20,
        ]);

        $comments = $query->orderBy(['createdat' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        // Get stats
        $pendingCount = BlogNestedComment::find()->where(['status' => 'pending'])->count();
        $approvedCount = BlogNestedComment::find()->where(['status' => 'approved'])->count();
        $spamCount = BlogNestedComment::find()->where(['status' => 'spam'])->count();

        return $this->render('comments', [
            'pendingComments' => $comments,
            'pagination' => $pagination,
            'statusFilter' => $statusFilter,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'spamCount' => $spamCount,
        ]);
    }

    /**
     * Duyệt bình luận nested
     */
    public function actionApproveComment($id)
    {
        $comment = BlogNestedComment::findOne($id);

        if ($comment === null) {
            throw new NotFoundHttpException('Bình luận không tồn tại.');
        }

        $comment->status = BlogNestedComment::STATUS_APPROVED;
        if ($comment->save()) {
            Yii::$app->session->setFlash('success', 'Bình luận được duyệt thành công!');
        }

        return $this->redirect(['admin/comments']);
    }

    /**
     * Từ chối bình luận nested
     */
    public function actionRejectComment($id)
    {
        $comment = BlogNestedComment::findOne($id);

        if ($comment === null) {
            throw new NotFoundHttpException('Bình luận không tồn tại.');
        }

        $comment->status = BlogNestedComment::STATUS_REJECTED;
        if ($comment->save()) {
            Yii::$app->session->setFlash('success', 'Bình luận bị từ chối.');
        }

        return $this->redirect(['admin/comments']);
    }

    /**
     * Đánh dấu spam
     */
    public function actionMarkSpam($id)
    {
        $comment = BlogNestedComment::findOne($id);

        if ($comment === null) {
            throw new NotFoundHttpException('Bình luận không tồn tại.');
        }

        $comment->status = 'spam';
        if ($comment->save()) {
            Yii::$app->session->setFlash('success', 'Bình luận được đánh dấu là spam.');
        }

        return $this->redirect(['admin/comments']);
    }

    /**
     * Xóa bình luận nested
     */
    public function actionDeleteComment($id)
    {
        $comment = BlogNestedComment::findOne($id);

        if ($comment === null) {
            throw new NotFoundHttpException('Bình luận không tồn tại.');
        }

        if ($comment->delete()) {
            Yii::$app->session->setFlash('success', 'Bình luận được xóa thành công!');
        }

        return $this->redirect(['admin/comments']);
    }

    /**
     * Tìm model BlogPost dựa trên ID
     */
    protected function findBlogPost($id)
    {
        $model = BlogPost::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        return $model;
    }
}
