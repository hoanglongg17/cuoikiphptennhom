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
                'only' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin'],
                'rules' => [
                    [
                        'actions' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin'],
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
                    'blog-pin' => ['POST'],
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

        // Lấy 5 bài viết mới nhất
        $recentPosts = BlogPost::find()
            ->orderBy(['createdat' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'draftPosts' => $draftPosts,
            'recentPosts' => $recentPosts,
        ]);
    }

    /**
     * Danh sách các bài viết blog
     */
    public function actionBlogList()
    {
        $status = Yii::$app->request->get('status', '');
        $keyword = Yii::$app->request->get('q', '');
        
        $query = BlogPost::find();
        
        if ($status) {
            $query->where(['status' => $status]);
        }

        if (!empty($keyword)) {
            $query->andWhere([
                'or',
                ['like', 'title', $keyword],
                ['like', 'content', $keyword],
                ['like', 'excerpt', $keyword],
            ]);
        }

        $posts = $query->orderBy(['createdat' => SORT_DESC])
            ->all();

        return $this->render('blog-list', [
            'posts' => $posts,
            'currentStatus' => $status,
            'keyword' => $keyword,
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

        if ($model->load(Yii::$app->request->post())) {
            // Nếu admin chọn xuất bản ngay, đặt publishedat
            if ($model->status === BlogPost::STATUS_PUBLISHED && is_null($model->publishedat)) {
                $model->publishedat = date('Y-m-d H:i:s');
            } elseif ($model->status === BlogPost::STATUS_DRAFT) {
                $model->publishedat = null;
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết được tạo thành công!');
                return $this->redirect(['blog-list']);
            }
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

        if ($model->load(Yii::$app->request->post())) {
            // Nếu chuyển từ draft sang published, đặt publishedat
            if ($model->status === BlogPost::STATUS_PUBLISHED && is_null($model->publishedat)) {
                $model->publishedat = date('Y-m-d H:i:s');
            } elseif ($model->status === BlogPost::STATUS_DRAFT) {
                $model->publishedat = null;
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết được cập nhật thành công!');
                return $this->redirect(['blog-list']);
            }
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
     * Ghim/Bỏ ghim bài viết
     */
    public function actionBlogPin($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = $this->findBlogPost($id);
        $model->is_pinned = !$model->is_pinned;
        
        if ($model->save()) {
            return [
                'success' => true,
                'isPinned' => $model->is_pinned,
                'message' => $model->is_pinned ? 'Bài viết đã được ghim' : 'Bài viết đã được bỏ ghim',
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra',
        ];
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
