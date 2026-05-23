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
use app\models\AdminForm;
use yii\data\Pagination;


class AdminController extends Controller
{
    public $layout = 'main';

    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin', 'blog-approve', 'blog-reject', 'blog-archive', 'blog-unarchive', 'blog-comments', 'approve-comment', 'reject-comment', 'delete-comment', 'admin-list', 'admin-create', 'admin-detail', 'admin-delete'],
                'rules' => [
                        [
                            'actions' => ['dashboard', 'blog-list', 'blog-edit', 'blog-create', 'blog-delete', 'blog-pin', 'blog-approve', 'blog-reject', 'blog-archive', 'blog-unarchive', 'blog-comments', 'approve-comment', 'reject-comment', 'delete-comment', 'admin-list', 'admin-create', 'admin-detail', 'admin-delete'],
                        'allow' => true,
                        'roles' => ['@'],  
                        'matchCallback' => function ($rule, $action) {
                            
                            
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
                    'admin-delete' => ['POST', 'DELETE'],
                ],
            ],
        ];
    }

    
    public function actionDashboard()
    {
        
        $totalPosts = BlogPost::find()->count();
        $publishedPosts = BlogPost::find()->where(['status' => BlogPost::STATUS_PUBLISHED])->count();
        $pendingPosts = BlogPost::find()->where(['status' => BlogPost::STATUS_PENDING])->count();

        
        $recentPosts = BlogPost::find()
            ->where(['status' => BlogPost::STATUS_PUBLISHED])
            ->orderBy(['createdat' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'pendingPosts' => $pendingPosts,
            'draftPosts' => $pendingPosts,
            'recentPosts' => $recentPosts,
        ]);
    }

    
    public function actionBlogList()
    {
        $status = Yii::$app->request->get('status', '');
        $keyword = Yii::$app->request->get('q', '');
        
        $query = BlogPost::find();

        if ($status === BlogPost::STATUS_DRAFT) {
            
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

    
    public function actionBlogCreate()
    {
        $model = new BlogPost();
        $model->userid = Yii::$app->user->id;
        $model->status = BlogPost::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post())) {
            
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

    
    public function actionBlogEdit($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->load(Yii::$app->request->post())) {
            
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

    
    public function actionBlogDelete($id)
    {
        $model = $this->findBlogPost($id);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Bài viết được xóa thành công!');
        }

        return $this->redirect(['blog-list']);
    }

    
    public function actionDeleteComment($id)
    {
        
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

    
    public function actionAdminList()
    {
        $admins = User::find()->where(['role' => 'admin'])->all();
        
        return $this->render('admin-list', [
            'admins' => $admins,
        ]);
    }

    
    public function actionAdminCreate()
    {
        $model = new AdminForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($admin = $model->createAdmin()) {
                Yii::$app->session->setFlash('success', 'Tài khoản Admin đã được tạo thành công!');
                return $this->redirect(['admin-list']);
            } else {
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra khi tạo tài khoản Admin.');
            }
        }

        return $this->render('admin-form', [
            'model' => $model,
        ]);
    }

    
    public function actionAdminDetail($id)
    {
        $admin = User::findOne(['userid' => $id, 'role' => 'admin']);
        
        if (!$admin) {
            throw new NotFoundHttpException('Admin không tồn tại.');
        }

        $blogPosts = $admin->getBlogPosts()
            ->where(['!=', 'status', BlogPost::STATUS_DRAFT])
            ->all();

        return $this->render('admin-detail', [
            'admin' => $admin,
            'blogPosts' => $blogPosts,
        ]);
    }

    
    public function actionAdminDelete($id)
    {
        $currentUser = Yii::$app->user->identity;
        $admin = User::findOne(['userid' => $id, 'role' => 'admin']);

        if (!$admin) {
            throw new NotFoundHttpException('Admin không tồn tại.');
        }

        
        if ($admin->userid === $currentUser->userid) {
            Yii::$app->session->setFlash('error', 'Không thể xóa tài khoản của chính mình!');
            return $this->redirect(['admin-list']);
        }

        
        $publishedPosts = $admin->getBlogPosts()
            ->where(['status' => BlogPost::STATUS_PUBLISHED])
            ->count();

        if ($publishedPosts > 0) {
            Yii::$app->session->setFlash('error', 'Không thể xóa admin khi còn có bài viết đã xuất bản!');
            return $this->redirect(['admin-detail', 'id' => $id]);
        }

        
        $otherPosts = $admin->getBlogPosts()
            ->where(['!=', 'status', BlogPost::STATUS_PUBLISHED])
            ->all();
        
        foreach ($otherPosts as $post) {
            $post->delete();
        }

        
        if ($admin->delete()) {
            Yii::$app->session->setFlash('success', 'Tài khoản Admin và các bài viết không xuất bản của admin đó đã được xóa thành công!');
        } else {
            Yii::$app->session->setFlash('error', 'Có lỗi xảy ra khi xóa tài khoản Admin.');
        }

        return $this->redirect(['admin-list']);
    }
    
    protected function findBlogPost($id)
    {
        $model = BlogPost::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Bài viết không tồn tại.');
        }

        return $model;
    }
}
