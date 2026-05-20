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
                'only' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin', 'blog-approve', 'blog-reject', 'blog-archive', 'blog-unarchive', 'blog-comments', 'approve-comment', 'reject-comment', 'delete-comment'],
                'rules' => [
                        [
                            'actions' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin', 'blog-approve', 'blog-reject', 'blog-archive', 'blog-unarchive', 'blog-comments', 'approve-comment', 'reject-comment', 'delete-comment'],
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
                    'blog-approve' => ['POST'],
                    'blog-reject' => ['POST'],
                    'blog-archive' => ['POST'],
                    'blog-unarchive' => ['POST'],
                    'blog-pin' => ['POST'],
                    'delete-comment' => ['POST'],
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

        // Lấy 5 bài viết mới nhất đã xuất bản
        $recentPosts = BlogPost::find()
            ->where(['status' => BlogPost::STATUS_PUBLISHED])
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

        if ($status === BlogPost::STATUS_DRAFT) {
            // Admin không xem bài draft của user
            $query->where(['postid' => 0]);
        } elseif ($status) {
            $query->where(['status' => $status]);
        } else {
            $query->where(['not', ['status' => BlogPost::STATUS_DRAFT]]);
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
     * Duyệt bài viết chờ duyệt
     */
    public function actionBlogApprove($id)
    {
        $model = $this->findBlogPost($id);

        if (in_array($model->status, [BlogPost::STATUS_PENDING, BlogPost::STATUS_DENIED], true)) {
            $model->status = BlogPost::STATUS_PUBLISHED;
            if (is_null($model->publishedat)) {
                $model->publishedat = date('Y-m-d H:i:s');
            }
            $model->rejectionreason = null;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết đã được duyệt và xuất bản.');
            }
        }

        return $this->redirect(['blog-list']);
    }

    /**
     * Từ chối bài viết
     */
    public function actionBlogReject($id)
    {
        $model = $this->findBlogPost($id);
        $reason = Yii::$app->request->post('rejectionreason', '');
        $isAjax = Yii::$app->request->isAjax;

        if (in_array($model->status, [BlogPost::STATUS_PENDING, BlogPost::STATUS_DRAFT, BlogPost::STATUS_DENIED], true)) {
            $model->status = BlogPost::STATUS_DENIED;
            $model->publishedat = null;
            $model->rejectionreason = trim($reason);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết đã bị từ chối.');
                if ($isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'Bài viết đã bị từ chối.',
                    ];
                }
            }
        }

        if ($isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => false,
                'message' => 'Không thể từ chối bài viết.',
            ];
        }

        return $this->redirect(['blog-list']);
    }

    /**
     * Lưu trữ bài viết
     */
    public function actionBlogArchive($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->status === BlogPost::STATUS_PUBLISHED) {
            $model->status = BlogPost::STATUS_ARCHIVED;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết đã được lưu trữ.');
            }
        }

        return $this->redirect(['blog-list']);
    }

    /**
     * Khôi phục bài viết lưu trữ về xuất bản
     */
    public function actionBlogUnarchive($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->status === BlogPost::STATUS_ARCHIVED) {
            $model->status = BlogPost::STATUS_PUBLISHED;
            if (is_null($model->publishedat)) {
                $model->publishedat = date('Y-m-d H:i:s');
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Bài viết đã được đưa về trạng thái xuất bản.');
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
     * Xóa bình luận (admin)
     */
    public function actionDeleteComment($id)
    {
        // Only admin reaches here due to access rules
        $nested = BlogNestedComment::findOne($id);
        if ($nested) {
            $nested->delete();
            Yii::$app->session->setFlash('success', 'Bình luận đã được xóa');
            return $this->redirect(['blog-comments']);
        }

        $comment = BlogComment::findOne($id);
        if ($comment) {
            $comment->delete();
            Yii::$app->session->setFlash('success', 'Bình luận đã được xóa');
            return $this->redirect(['blog-comments']);
        }

        Yii::$app->session->setFlash('error', 'Bình luận không tồn tại');
        return $this->redirect(['blog-comments']);
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
