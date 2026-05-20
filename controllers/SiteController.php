<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;
use app\models\SignupForm;
use yii\helpers\FileHelper;


use app\models\Deck;
use app\models\Card;
use app\models\CardProgress;
use app\models\ReviewLog;
use app\models\DeckSettings;
use app\helpers\SM2Helper;

class SiteController extends Controller
{
    
    public $layout = 'main';

    
    public function beforeAction($action)
    {
        
        if (strpos($action->id, 'ajax-') === 0 || Yii::$app->request->isAjax) {
            $this->enableCsrfValidation = false;
        }

        
        if (in_array($action->id, ['index', 'login', 'signup'])) {
            $this->layout = 'landing';
        }

        return parent::beforeAction($action);
    }

    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                
                'only' => [
                    'logout', 'dashboard', 'signup', 'login', 'vocabset', 'vocabulary',
                    'practice', 'study-deck',
                    'ajax-create-deck', 'ajax-update-deck', 'ajax-delete-deck',
                    'ajax-delete-card', 'ajax-remove-from-deck', 'ajax-import-deck', 
                    'ajax-assign-card-to-deck', 'ajax-save-batch-cards', 'ajax-grade-card', 'ajax-get-next-card',
                    'ajax-update-card', 'ajax-update-profile',
                    'error', 'captcha', 'auth',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'dashboard', 'logout', 'vocabset', 'vocabulary',
                            'practice', 'study-deck',
                            'ajax-create-deck', 'ajax-update-deck', 'ajax-delete-deck',
                            'ajax-delete-card', 'ajax-remove-from-deck', 'ajax-import-deck', 
                            'ajax-assign-card-to-deck', 'ajax-save-batch-cards', 'ajax-grade-card', 'ajax-get-next-card',
                            'ajax-update-card', 'ajax-update-profile',
                        ],
                        'allow' => true,
                        'roles' => ['@'], 
                    ],
                    [
                        'actions' => ['signup', 'login', 'index', 'captcha', 'auth'],
                        'allow' => true,
                        'roles' => ['?'], 
                    ],
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['@', '?'],
                    ],
                ],
                
                
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        Yii::$app->response->data = ['success' => false, 'message' => 'Phiên đăng nhập hết hạn.'];
                        Yii::$app->response->send();
                        Yii::$app->end();
                    }
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'], 
                ],
            ],
        ];
    }

    
    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    
    public function actionIndex()
    {
        
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }
        
        $this->layout = 'landing';
        return $this->render('index');
    }

    

    
    public function actionLogin()
    {
        
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }

        $model = new LoginForm();

        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                
                return $this->redirect(['site/dashboard']);
            } else {
                
                Yii::error("Đăng nhập thất bại cho email: " . $model->email);
            }
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    
    public function actionDashboard()
    {
        
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('dashboard');
    }

    
    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();
        $user = User::findByGoogleId($attributes['id']);

        if (!$user) {
            $user = User::findByEmail($attributes['email']);
            if ($user) {
                $user->googleid = (string)$attributes['id'];
                $user->save(false);
            } else {
                $user = new User();
                $user->displayname = $attributes['name'];
                $user->email = $attributes['email'];
                $user->googleid = (string)$attributes['id'];
                $user->avatarurl = $attributes['picture'] ?? null;
                $user->save(false);
            }
        }

        Yii::$app->user->login($user, 3600 * 24 * 30);
    }
    
    
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']); 
    }

    
    public function actionSignup()
    {
        
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }

        $model = new SignupForm();

        
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                
                if (Yii::$app->user->login($user)) {
                    return $this->redirect(['site/dashboard']);
                }
            }
        }

        
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    

    
    public function actionVocabset()
    {
        
        $userId = Yii::$app->user->id;
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->with(['cards.progress']) 
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        
        $today = date('Y-m-d');
        $deckQuotas = [];
        
        foreach ($decks as $deck) {
            
            $deckSettings = DeckSettings::findOne(['deckid' => $deck->deckid]) ?: new DeckSettings();
            $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
            $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

            
            $todayNewCount = ReviewLog::find()
                ->joinWith('card')
                ->joinWith('cardProgress')
                ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
                ->andWhere(['cards.deckid' => $deck->deckid, 'cards.userid' => $userId])
                ->andWhere(['cardprogress.status' => 0])
                ->count();

            $todayReviewCount = ReviewLog::find()
                ->joinWith('card')
                ->joinWith('cardProgress')
                ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
                ->andWhere(['cards.deckid' => $deck->deckid, 'cards.userid' => $userId])
                ->andWhere(['cardprogress.status' => 2])
                ->count();

            $newQuotaRemaining = $maxNewCards - $todayNewCount;
            $reviewQuotaRemaining = $maxReviewCards - $todayReviewCount;

            
            $deckQuotas[$deck->deckid] = [
                'newRemaining' => max(0, $newQuotaRemaining),
                'reviewRemaining' => max(0, $reviewQuotaRemaining),
            ];
        }

        return $this->render('vocabset', [
            'decks' => $decks,
            'deckQuotas' => $deckQuotas,
        ]);
    }

    
    public function actionVocabulary($deck_id = null)
    {
        $userId = Yii::$app->user->id;

        
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        
        $query = Card::find()->where(['userid' => $userId])->with('progress')->orderBy(['createdat' => SORT_DESC]);

        
        if ($deck_id) {
            $query->andWhere(['deckid' => $deck_id]);
        }

        $cards = $query->all();

        
        $srsByLevel = [
            0 => ['name' => 'Từ mới', 'count' => 0, 'nextReview' => 'Học ngay', 'color' => '#2196F3'],
            1 => ['name' => 'Sau 1 ngày', 'count' => 0, 'nextReview' => '', 'color' => '#FF9800'],
            2 => ['name' => 'Sau 3 ngày', 'count' => 0, 'nextReview' => '', 'color' => '#FF6B6B'],
            3 => ['name' => 'Sau 7 ngày', 'count' => 0, 'nextReview' => '', 'color' => '#9C27B0'],
            4 => ['name' => 'Sau 14 ngày', 'count' => 0, 'nextReview' => '', 'color' => '#4CAF50'],
            5 => ['name' => 'Đã thuộc', 'count' => 0, 'nextReview' => 'Không ôn', 'color' => '#00BCD4'],
        ];

        foreach ($cards as $card) {
            if (!$card->progress) {
                $srsByLevel[0]['count']++;
            } else {
                $intervalDays = $card->progress->intervaldays ?? 0;
                $status = $card->progress->status;

                if ($status == 0 || $status == 1) {
                    $srsByLevel[0]['count']++;
                } else if ($status == 2) {
                    if ($intervalDays <= 1) {
                        $level = 1;
                    } elseif ($intervalDays <= 3) {
                        $level = 2;
                    } elseif ($intervalDays <= 7) {
                        $level = 3;
                    } elseif ($intervalDays < 14) {
                        $level = 4;
                    } else {
                        $level = 5;
                    }
                    $srsByLevel[$level]['count']++;

                    $dueDate = strtotime($card->progress->duedate);
                    $diffDays = ceil(($dueDate - strtotime('now')) / 86400);
                    if ($diffDays <= 0) {
                        $srsByLevel[$level]['nextReview'] = 'Due hôm nay';
                    } else {
                        $srsByLevel[$level]['nextReview'] = '+' . $diffDays . ' ngày';
                    }
                }
            }
        }

        return $this->render('vocabulary', [
            'decks' => $decks,
            'cards' => $cards,
            'currentDeckId' => $deck_id,
            'srsByLevel' => $srsByLevel,
        ]);
    }

    

    
    public function actionAjaxDeleteCard($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = $id ?? Yii::$app->request->post('id') ?? Yii::$app->request->get('id');
        $userId = Yii::$app->user->id;

        if (!$id) {
            return ['success' => false, 'message' => 'Không tìm thấy ID thẻ.'];
        }

        $model = Card::findOne(['cardid' => $id, 'userid' => $userId]);
        if ($model && $model->delete()) {
            return ['success' => true, 'message' => 'Đã xóa thẻ vĩnh viễn.'];
        }
        return ['success' => false, 'message' => 'Lỗi: Không tìm thấy thẻ hoặc không có quyền xóa.'];
    }
    public function actionAjaxUpdateCard()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $userId = Yii::$app->user->id;

            $model = Card::findOne(['cardid' => $data['cardid'] ?? null, 'userid' => $userId]);
            if (!$model) return ['success' => false, 'message' => 'Không tìm thấy thẻ.'];

            $model->frontcontent = trim($data['frontcontent'] ?? $model->frontcontent);
            $model->backcontent = trim($data['backcontent'] ?? $model->backcontent);
            $model->pronunciation = trim($data['pronunciation'] ?? $model->pronunciation);
            $model->examplesentence = trim($data['examplesentence'] ?? $model->examplesentence);
            $model->tags = trim($data['tags'] ?? $model->tags);

            if ($model->save()) {
                return ['success' => true, 'message' => 'Cập nhật từ vựng thành công!'];
            }

            $errorMsg = reset($model->errors)[0] ?? 'Lỗi khi cập nhật dữ liệu.';
            return ['success' => false, 'message' => $errorMsg];
        }
    
    public function actionAjaxRemoveFromDeck($id = null) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = $id ?? Yii::$app->request->post('id') ?? Yii::$app->request->get('id');
        $userId = Yii::$app->user->id;

        $model = Card::findOne(['cardid' => $id, 'userid' => $userId]);
        if ($model) {
            $model->deckid = null; 
            if ($model->save(false)) {
                return ['success' => true, 'message' => 'Đã gỡ thẻ thành công.'];
            }
        }
        return ['success' => false, 'message' => 'Lỗi: Không thể gỡ thẻ.'];
    }
    public function actionAjaxImportDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Vui lòng đăng nhập để thêm bộ thẻ.'];
        }

        $deckId = Yii::$app->request->post('deckId');
        $userId = Yii::$app->user->id;

        
        $originalDeck = Deck::find()->where(['deckid' => $deckId])->with('cards')->one();

        if (!$originalDeck) {
            return ['success' => false, 'message' => 'Không tìm thấy bộ bài với ID: ' . $deckId];
        }

        
        if ($originalDeck->userid == $userId) {
            return ['success' => false, 'message' => 'Bạn không thể nhập bộ bài của chính mình.'];
        }

        
        $existsExact = Deck::findOne(['userid' => $userId, 'name' => $originalDeck->name]);
        $existsImported = Deck::findOne(['userid' => $userId, 'name' => $originalDeck->name . ' (Đã nhập)']);
        if ($existsExact || $existsImported) {
            return ['success' => false, 'message' => 'Bạn đã có bộ thẻ này'];
        }

        
        $newDeck = new Deck();
        $newDeck->name = $originalDeck->name . " (Đã nhập)";
        $newDeck->description = $originalDeck->description;
        $newDeck->userid = $userId;

        if ($newDeck->save()) {
            
            foreach ($originalDeck->cards as $card) {
                $newCard = new Card();
                $newCard->userid = $userId;
                $newCard->deckid = $newDeck->deckid;
                $newCard->cardtype = $card->cardtype;
                $newCard->frontcontent = $card->frontcontent;
                $newCard->backcontent = $card->backcontent;
                $newCard->pronunciation = $card->pronunciation;
                $newCard->examplesentence = $card->examplesentence;
                $newCard->tags = $card->tags;
                $newCard->save(false);
            }
            return ['success' => true, 'message' => 'Đã thêm bộ thẻ: ' . $originalDeck->name, 'newDeckId' => $newDeck->deckid];
        }

        return ['success' => false, 'message' => 'Có lỗi xảy ra khi lưu dữ liệu bộ bài mới.'];
    }
    
     public function actionAjaxUpdateDeck($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $id ?? Yii::$app->request->post('id');
        $userId = Yii::$app->user->id;

        $model = Deck::findOne(['deckid' => $id, 'userid' => $userId]);
        if (!$model) {
            return ['success' => false, 'message' => 'Không tìm thấy bộ thẻ.'];
        }

        $data = Yii::$app->request->post();
        $newName = trim($data['name'] ?? $model->name);

        
        if ($newName !== $model->name) {
            $exists = Deck::findOne(['name' => $newName, 'userid' => $userId]);
            if ($exists) {
                return ['success' => false, 'message' => 'Bạn đã có bộ thẻ với tên này rồi. Vui lòng chọn tên khác!'];
            }
        }

        $model->name = $newName;
        $model->description = trim($data['description'] ?? $model->description);

        try {
            if ($model->save()) {
                return ['success' => true, 'message' => 'Đã lưu thay đổi thành công!'];
            }
            $errorMsg = reset($model->errors)[0] ?? 'Lỗi khi cập nhật bộ thẻ.';
            return ['success' => false, 'message' => $errorMsg];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu, không thể lưu.'];
        }
    }
    
    public function actionAjaxDeleteDeck($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $id ?? Yii::$app->request->post('id');
        $model = Deck::findOne(['deckid' => $id, 'userid' => Yii::$app->user->id]);
        if ($model && $model->delete()) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    
   public function actionAjaxCreateDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $name = trim($data['name'] ?? 'Bộ bài mới');
        $userId = Yii::$app->user->id; 

        
        $exists = Deck::findOne(['name' => $name, 'userid' => $userId]);
        if ($exists) {
            return ['success' => false, 'message' => 'Bạn đã có bộ thẻ với tên này rồi. Vui lòng chọn tên khác!'];
        }

        $model = new Deck();
        $model->name = $name;
        
        $model->description = trim($data['description'] ?? '');
        $model->userid = $userId; 
        
        try {
            if ($model->save()) {
                
                $setting = new DeckSettings();
                $setting->deckid = $model->deckid;
                $setting->save(false);

                return ['success' => true, 'message' => 'Tạo bộ thẻ mới thành công!'];
            }
            
            
            $errorMsg = reset($model->errors)[0] ?? 'Lỗi không xác định khi tạo bộ thẻ.';
            return ['success' => false, 'message' => 'Lỗi dữ liệu: ' . $errorMsg];
            
        } catch (\Exception $e) {
            
            return ['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()];
        }
    }

    
    public function actionAjaxTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::info('AJAX Test called', __METHOD__);
        return ['success' => true, 'message' => 'AJAX working!', 'time' => date('Y-m-d H:i:s')];
    }

    
    public function actionAjaxUpdateProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        
        Yii::info('AJAX Update Profile called', __METHOD__);
        
        if (Yii::$app->user->isGuest) {
            Yii::warning('User is guest', __METHOD__);
            return ['success' => false, 'message' => 'Phiên đăng nhập hết hạn.'];
        }

        
        /** @var app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();

        Yii::info('Update data: ' . json_encode($post), __METHOD__);

        if (!empty($post['displayname'])) {
            $user->displayname = $post['displayname'];
            Yii::info('Updated displayname to: ' . $post['displayname'], __METHOD__);
        }
        
        if (!empty($post['password'])) {
            $user->setPassword($post['password']);
            Yii::info('Password updated', __METHOD__);
        }

        
        if (!empty($post['avatar_base64'])) {
            try {
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
                if (!is_dir($uploadPath)) FileHelper::createDirectory($uploadPath);

                if (preg_match('/^data:image\/(\w+);base64,/', $post['avatar_base64'], $type)) {
                    $data = base64_decode(substr($post['avatar_base64'], strpos($post['avatar_base64'], ',') + 1));
                    if ($data !== false) {
                        $fileName = 'avatar_' . $user->id . '_' . time() . '.' . strtolower($type[1]);
                        if (file_put_contents($uploadPath . DIRECTORY_SEPARATOR . $fileName, $data)) {
                            $user->avatarurl = Yii::getAlias('@web/uploads/avatars/') . $fileName;
                            Yii::info('Avatar saved: ' . $fileName, __METHOD__);
                        }
                    }
                }
            } catch (\Exception $e) { 
                Yii::error('Avatar error: ' . $e->getMessage(), __METHOD__);
            }
        }

        if ($user->save(false)) {
            Yii::info('Profile saved successfully', __METHOD__);
            return [
                'success' => true,
                'displayname' => $user->displayname,
                'avatarurl' => $user->avatarurl
            ];
        }
        
        Yii::error('Profile save failed. Errors: ' . json_encode($user->getErrors()), __METHOD__);
        return ['success' => false, 'message' => 'Không thể lưu thông tin.'];
    }

    
    public function actionAjaxSaveBatchCards()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request->post();
        $deckId = $request['deckId'] ?? null;
        $cardsData = json_decode($request['cards'], true);
        $userId = Yii::$app->user->id;

        if (!$deckId || empty($cardsData)) return ['success' => false, 'message' => 'Dữ liệu rỗng.'];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($cardsData as $data) {
                $this->saveCardInstance($deckId, $data, $request['cardType'] ?? 1, $userId);
            }
            $transaction->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            if ($transaction) $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    
    private function saveCardInstance($deckId, $data, $type, $userId) {
        $model = new Card();
        $model->userid = $userId;
        $model->deckid = $deckId;
        $model->cardtype = $type; 
        $model->frontcontent = $data['front'] ?? '';
        $model->backcontent = $data['back'] ?? '';
        $model->pronunciation = $data['pronunciation'] ?? '';
        $model->examplesentence = $data['example'] ?? '';
        $model->tags = $data['tags'] ?? '';
        $model->createdat = date('Y-m-d H:i:s');
        if (!$model->save()) throw new \Exception("Lỗi lưu dữ liệu thẻ.");
    }

    
    public function actionAjaxAssignCardToDeck() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cardId = Yii::$app->request->post('cardId');
        $newDeckId = Yii::$app->request->post('deckId');
        $userId = Yii::$app->user->id;

        $card = Card::findOne(['cardid' => $cardId, 'userid' => $userId]);
        if (!$card || !$newDeckId) return ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];

        if ($card->deckid === null) {
            $card->deckid = $newDeckId;
            $card->save(false);
        } else {
            $newCard = new Card();
            $newCard->attributes = $card->attributes; 
            $newCard->cardid = null; 
            $newCard->deckid = $newDeckId;
            $newCard->userid = $userId;
            $newCard->createdat = date('Y-m-d H:i:s');
            $newCard->save(false);
        }
        return ['success' => true];
    }

    

    
    public function actionPractice()
    {
        $userId = Yii::$app->user->id;
        $today = date('Y-m-d');
        
        
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->with(['cards', 'cards.progress'])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        
        $deckStats = [];
        foreach ($decks as $deck) {
            
            $deckSettings = DeckSettings::findOne(['deckid' => $deck->deckid]) ?: new DeckSettings();
            $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
            $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

            
            $todayNewCount = ReviewLog::find()
                ->joinWith('card')
                ->joinWith('cardProgress')
                ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
                ->andWhere(['cards.deckid' => $deck->deckid, 'cards.userid' => $userId])
                ->andWhere(['cardprogress.status' => 0])
                ->count();

            $todayReviewCount = ReviewLog::find()
                ->joinWith('card')
                ->joinWith('cardProgress')
                ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
                ->andWhere(['cards.deckid' => $deck->deckid, 'cards.userid' => $userId])
                ->andWhere(['cardprogress.status' => 2])
                ->count();

            
            $newQuotaRemaining = $maxNewCards - $todayNewCount;
            $reviewQuotaRemaining = $maxReviewCards - $todayReviewCount;

            
            $new = 0;
            $learning = 0;
            $review = 0;

            foreach ($deck->cards as $card) {
                $progress = $card->progress;
                if (!$progress) {
                    
                    if ($newQuotaRemaining > 0) {
                        $new++;
                        $newQuotaRemaining--;
                    }
                } else {
                    $status = $progress->status;
                    
                    $isDue = strtotime($progress->duedate) <= strtotime($today . ' 23:59:59');
                    
                    if ($status == 0) {
                        
                        if ($isDue && $newQuotaRemaining > 0) {
                            $new++;
                            $newQuotaRemaining--;
                        }
                    } elseif ($status == 1) {
                        
                        if ($isDue) {
                            $learning++;
                        }
                    } elseif ($status == 2) {
                        
                        if ($isDue && $reviewQuotaRemaining > 0) {
                            $review++;
                            $reviewQuotaRemaining--;
                        }
                    }
                }
            }

            $deckStats[$deck->deckid] = [
                'new' => $new,
                'learning' => $learning,
                'review' => $review,
                'total' => count($deck->cards),
            ];
        }

        return $this->render('practice', [
            'decks' => $decks,
            'deckStats' => $deckStats,
        ]);
    }

    
    public function actionStudyDeck($deckid = null)
    {
        if (!$deckid) return $this->redirect(['site/practice']);

        $userId = Yii::$app->user->id;
        $deck = Deck::findOne(['deckid' => $deckid, 'userid' => $userId]);

        if (!$deck) return $this->redirect(['site/practice']);

        
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        
        $cardsToStudy = Card::find()
            ->where(['userid' => $userId, 'deckid' => $deckid])
            ->with('progress')
            ->all();

        
        $deckSettings = DeckSettings::findOne(['deckid' => $deckid]) ?: new DeckSettings();
        $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
        $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

        
        $today = date('Y-m-d');
        $todayNewCount = ReviewLog::find()
            ->joinWith('card')
            ->joinWith('cardProgress')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckid, 'cards.userid' => $userId])
            ->andWhere(['cardprogress.status' => 0])
            ->count();

        $todayReviewCount = ReviewLog::find()
            ->joinWith('card')
            ->joinWith('cardProgress')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckid, 'cards.userid' => $userId])
            ->andWhere(['cardprogress.status' => 2])
            ->count();

        
        $newQuotaRemaining = $maxNewCards - $todayNewCount;
        $reviewQuotaRemaining = $maxReviewCards - $todayReviewCount;

        
        $dueSoon = [];
        $new = [];
        $learning = [];
        $review = [];

        foreach ($cardsToStudy as $card) {
            $progress = $card->progress;
            if (!$progress) {
                $new[] = $card;
            } else {
                
                $isDue = strtotime($progress->duedate) <= strtotime($today . ' 23:59:59');
                if ($progress->status == 0) {
                    if ($isDue) $dueSoon[] = $card;
                    else $new[] = $card;
                } elseif ($progress->status == 1) {
                    if ($isDue) $dueSoon[] = $card;
                    else $learning[] = $card;
                } elseif ($progress->status == 2) {
                    if ($isDue) $dueSoon[] = $card;
                    else $review[] = $card;
                }
            }
        }

        
        $availableDue = [];
        foreach ($dueSoon as $card) {
            $progress = $card->progress;
            if ($progress && $progress->status == 2 && $reviewQuotaRemaining > 0) {
                $availableDue[] = $card;
                $reviewQuotaRemaining--;
            } elseif ($progress && $progress->status == 1) {
                
                $availableDue[] = $card;
            } elseif (!$progress && $newQuotaRemaining > 0) {
                $availableDue[] = $card;
                $newQuotaRemaining--;
            } elseif ($progress && $progress->status == 0 && $newQuotaRemaining > 0) {
                $availableDue[] = $card;
                $newQuotaRemaining--;
            }
        }

        
        $availableNew = [];
        foreach ($new as $card) {
            if ($newQuotaRemaining > 0) {
                $availableNew[] = $card;
                $newQuotaRemaining--;
            }
        }

        
        $availableLearning = $learning;

        
        
        $priorityQueue = array_merge($availableDue, $availableNew, $availableLearning);

        
        if (empty($priorityQueue)) {
            Yii::$app->session->setFlash('info', 'Hôm nay bạn đã hoàn thành tất cả bộ này! 🎉');
            return $this->redirect(['site/practice']);
        }

        $currentCard = $priorityQueue[0];
        $cardIndex = 1;
        $totalCards = count($priorityQueue);

        return $this->render('study-deck', [
            'deck' => $deck,
            'decks' => $decks,
            'currentCard' => $currentCard,
            'cardIndex' => $cardIndex,
            'totalCards' => $totalCards,
            'priorityQueue' => $priorityQueue,
        ]);
    }

    
    public function actionAjaxGetNextCard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $deckId = Yii::$app->request->post('deckId');
        $currentCardId = Yii::$app->request->post('currentCardId');
        $userId = Yii::$app->user->id;

        
        $deckSettings = DeckSettings::findOne(['deckid' => $deckId]) ?: new DeckSettings();
        $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
        $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

        
        $today = date('Y-m-d');
        $todayReviews = ReviewLog::find()
            ->joinWith('card')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckId, 'cards.userid' => $userId])
            ->count();

        
        $todayNewCount = ReviewLog::find()
            ->joinWith('card')
            ->joinWith('cardProgress')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckId, 'cards.userid' => $userId])
            ->andWhere(['cardprogress.status' => 0])
            ->count();

        $todayReviewCount = ReviewLog::find()
            ->joinWith('card')
            ->joinWith('cardProgress')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckId, 'cards.userid' => $userId])
            ->andWhere(['cardprogress.status' => 2])
            ->count();

        
        $cardsToStudy = Card::find()
            ->where(['userid' => $userId, 'deckid' => $deckId])
            ->with('progress')
            ->all();

        $dueSoon = [];
        $new = [];
        $learning = [];
        $review = [];

        foreach ($cardsToStudy as $card) {
            $progress = $card->progress;
            if (!$progress) {
                $new[] = $card;
            } else {
                
                if ($progress->status == 0) {
                    $dueSoon[] = $card; 
                } elseif ($progress->status == 1 && $progress->isDue()) {
                    $learning[] = $card; 
                } elseif ($progress->status == 2 && $progress->isDue()) {
                    $dueSoon[] = $card; 
                }
            }
        }

        
        
        
        $availableDue = $dueSoon;
        $availableNew = $new;

        
        $priorityQueue = array_merge($availableDue, $availableNew, $learning);

        
        
        $nextCard = null;
        $skipFirst = true;
        foreach ($priorityQueue as $card) {
            if ($card->cardid != $currentCardId) {
                $nextCard = $card;
                break;
            } elseif ($card->cardid == $currentCardId && !$skipFirst) {
                
                $nextCard = $card;
                break;
            }
        }
        
        
        
        if (!$nextCard) {
            foreach ($priorityQueue as $card) {
                if ($card->cardid == $currentCardId) {
                    
                    $nextCard = $card;
                    break;
                }
            }
        }

        if (!$nextCard) {
            
            return [
                'success' => true,
                'finished' => true,
                'message' => 'Hoàn thành tất cả thẻ trong bộ này! 🎉'
            ];
        }

        
        $cardIndex = 1;
        foreach ($priorityQueue as $idx => $card) {
            if ($card->cardid == $nextCard->cardid) {
                $cardIndex = $idx + 1;
                break;
            }
        }

        return [
            'success' => true,
            'finished' => false,
            'card' => [
                'cardid' => $nextCard->cardid,
                'frontcontent' => $nextCard->frontcontent,
                'backcontent' => $nextCard->backcontent,
                'pronunciation' => $nextCard->pronunciation,
                'examplesentence' => $nextCard->examplesentence,
                'cardtype' => $nextCard->cardtype,
                'tags' => $nextCard->tags,
                'cardIndex' => $cardIndex,
                'totalCards' => count($priorityQueue),
                
                'status' => $nextCard->progress->status ?? 0,
                'intervaldays' => $nextCard->progress->intervaldays ?? 0,
                'repetitions' => $nextCard->progress->repetitions ?? 0,
                'easefactor' => $nextCard->progress->easefactor ?? 2.5,
            ]
        ];
    }

    
    public function actionAjaxGradeCard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $cardId = Yii::$app->request->post('cardId');
        $grade = Yii::$app->request->post('grade'); 
        $userId = Yii::$app->user->id;

        
        $card = Card::findOne(['cardid' => $cardId, 'userid' => $userId]);
        if (!$card) {
            return ['success' => false, 'message' => 'Thẻ không tìm thấy.'];
        }

        
        $progress = CardProgress::findOne(['cardid' => $cardId]);
        if (!$progress) {
            $progress = new CardProgress();
            $progress->cardid = $cardId;
            $progress->status = 0;
            $progress->duedate = date('Y-m-d H:i:s');
            $progress->intervaldays = 0;
            $progress->easefactor = 2.5;
            $progress->repetitions = 0;
        }

        
        $reviewLog = new \app\models\ReviewLog();
        $reviewLog->cardid = $cardId;
        $reviewLog->grade = $grade;
        $reviewLog->reviewdate = date('Y-m-d H:i:s');
        $reviewLog->save(false);

        
        $sm2Result = SM2Helper::calculateNextReview(
            $grade,
            $progress->status,
            $progress->repetitions,
            $progress->intervaldays ?: 0,
            $progress->easefactor,
            $progress->lapses ?? 0
        );

        
        $progress->status = $sm2Result['status'];
        $progress->repetitions = $sm2Result['repetitions'];
        $progress->intervaldays = $sm2Result['interval'];
        $progress->easefactor = $sm2Result['easeFactor'];
        $progress->lapses = $sm2Result['lapses'];
        $progress->duedate = $sm2Result['nextReview'];

        if ($progress->save()) {
            return [
                'success' => true,
                'message' => 'Đã cập nhật tiến độ.',
            ];
        }

        return ['success' => false, 'message' => 'Lỗi khi lưu tiến độ.'];
    }
}