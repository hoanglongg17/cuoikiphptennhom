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

/* * =========================================================================
 * KHAI BÁO CÁC MODEL QUAN TRỌNG ĐỂ FIX LỖI "CLASS DECK NOT FOUND"
 * =========================================================================
 */
use app\models\Deck;
use app\models\Card;
use app\models\CardProgress;
use app\models\ReviewLog;
use app\models\DeckSettings;
use app\helpers\SM2Helper;

class SiteController extends Controller
{
    // Mặc định dùng layout main cho dashboard/vocabset/vocabulary
    public $layout = 'main';

    /**
     * beforeAction: Thiết lập cấu hình tiền xử lý.
     * ĐÃ BỔ SUNG: Tắt CSRF cho AJAX để các chức năng Xóa/Sửa của bạn không bị lỗi "Kết nối máy chủ".
     */
    public function beforeAction($action)
    {
        // Vô hiệu hóa kiểm tra CSRF cho các yêu cầu AJAX (Fetch)
        if (strpos($action->id, 'ajax-') === 0 || Yii::$app->request->isAjax) {
            $this->enableCsrfValidation = false;
        }

        // Dành riêng layout cho các page public (landing/login/signup)
        if (in_array($action->id, ['index', 'login', 'signup'])) {
            $this->layout = 'landing';
        }

        return parent::beforeAction($action);
    }

    /**
     * Cấu hình quyền truy cập (GIỮ NGUYÊN PHONG CÁCH BẢN GỐC)
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                // ĐÃ CẬP NHẬT: Bảo vệ đầy đủ các Action quan trọng
                'only' => [
                    'logout', 'dashboard', 'signup', 'login', 'vocabset', 'vocabulary',
                    'practice', 'study-deck',
                    'ajax-create-deck', 'ajax-update-deck', 'ajax-delete-deck',
                    'ajax-delete-card', 'ajax-remove-from-deck', 'ajax-import-deck', 
                    'ajax-assign-card-to-deck', 'ajax-save-batch-cards', 'ajax-grade-card', 'ajax-get-next-card',
                    'ajax-update-card',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'dashboard', 'logout', 'vocabset', 'vocabulary',
                            'practice', 'study-deck',
                            'ajax-create-deck', 'ajax-update-deck', 'ajax-delete-deck',
                            'ajax-delete-card', 'ajax-remove-from-deck', 'ajax-import-deck', 
                            'ajax-assign-card-to-deck', 'ajax-save-batch-cards', 'ajax-grade-card', 'ajax-get-next-card',
                            'ajax-update-card',  
                        ],
                        'allow' => true,
                        'roles' => ['@'], // Chỉ cho phép người đã đăng nhập
                    ],
                    [
                        'actions' => ['signup', 'login', 'index', 'error', 'captcha', 'auth'],
                        'allow' => true,
                        'roles' => ['?'], // Chỉ cho phép khách (chưa đăng nhập)
                    ],
                ],
                // Xử lý khi người dùng cố tình truy cập trang bị cấm hoặc hết phiên
                // ĐÃ TỐI ƯU: Tránh lỗi xoay vòng 404 cho các yêu cầu AJAX
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
                    'logout' => ['post', 'get'], // Hỗ trợ cả hai để nút thoát của bạn luôn chạy
                ],
            ],
        ];
    }

    /**
     * Các Action mở rộng (GIỮ NGUYÊN BẢN GỐC)
     */
    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            // Xử lý Google Login
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * Trang chủ hiển thị Landing Page
     * Nếu đã đăng nhập, redirect to dashboard
     */
    public function actionIndex()
    {
        // Nếu đã đăng nhập, redirect to dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }
        
        $this->layout = 'landing';
        return $this->render('index');
    }

    /* =========================================================================
       PHẦN 1: LOGIC ĐĂNG NHẬP, ĐĂNG KÝ VÀ TÀI KHOẢN (GIỮ NGUYÊN PHONG CÁCH BẠN)
       ========================================================================= */

    /**
     * Logic Đăng nhập hệ thống
     */
    public function actionLogin()
    {
        // Nếu đã đăng nhập thành công trước đó, đẩy vào Dashboard luôn
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }

        $model = new LoginForm();

        // Kiểm tra dữ liệu POST gửi lên
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                // ĐĂNG NHẬP THÀNH CÔNG -> Chuyển hướng
                return $this->redirect(['site/dashboard']);
            } else {
                // Nếu login() trả về false, lỗi sẽ nằm trong $model->errors
                Yii::error("Đăng nhập thất bại cho email: " . $model->email);
            }
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Trang Dashboard (Bàn làm việc)
     */
    public function actionDashboard()
    {
        // Kiểm tra lại một lần nữa cho chắc chắn
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('dashboard');
    }

    /**
     * Xử lý sau khi Google Auth thành công
     */
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
    
    /**
     * Đăng xuất khỏi hệ thống
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']); // Thoát xong đưa về trang Login
    }

    /**
     * Trang Đăng ký tài khoản người dùng mới
     */
    public function actionSignup()
    {
        // 1. Nếu đã đăng nhập thì không cho vào trang đăng ký nữa
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/dashboard']);
        }

        $model = new SignupForm();

        // 2. Xử lý khi người dùng nhấn nút Submit (POST)
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                // Đăng ký thành công, tự động đăng nhập luôn
                if (Yii::$app->user->login($user)) {
                    return $this->redirect(['site/dashboard']);
                }
            }
        }

        // 3. Hiển thị form đăng ký (GET)
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /* =========================================================================
       PHẦN 2: QUẢN LÝ DỮ LIỆU ĐA NGƯỜI DÙNG (MULTITENANCY)
       ========================================================================= */

    /**
     * Trang hiển thị danh sách các bộ thẻ của người dùng
     */
    public function actionVocabset()
    {
        // Lấy đúng ID của người đang đăng nhập thay vì số 1 cố định
        $userId = Yii::$app->user->id;
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->with(['cards.progress']) // Eager load dữ liệu cards và progress từ DB
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        // Tính quota còn lại hôm nay cho mỗi deck
        $today = date('Y-m-d');
        $deckQuotas = [];
        
        foreach ($decks as $deck) {
            // Lấy cài đặt bộ thẻ
            $deckSettings = DeckSettings::findOne(['deckid' => $deck->deckid]) ?: new DeckSettings();
            $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
            $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

            // Đếm đã học hôm nay
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

            // Lưu quota vào array (thay vì set vào deck object)
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

    /**
     * Trang quản lý từ vựng chi tiết
     */
    public function actionVocabulary($deck_id = null)
    {
        $userId = Yii::$app->user->id;

        // 1. Lấy danh sách bộ thẻ của CHÍNH USER để làm Bộ lọc (Filter)
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        // 2. Truy vấn danh sách thẻ để hiển thị trong bảng
        $query = Card::find()->where(['userid' => $userId])->with('progress')->orderBy(['createdat' => SORT_DESC]);

        // Nếu người dùng có chọn bộ lọc
        if ($deck_id) {
            $query->andWhere(['deckid' => $deck_id]);
        }

        $cards = $query->all();

        // 3. Tính SRS Level Distribution (phân bố từ vựng theo cấp độ ôn tập)
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

    /* =========================================================================
       PHẦN 3: CÁC API AJAX - ĐÃ FIX LỖI NÚT XÓA / GỠ THẺ (NHẬN ID CHÍNH XÁC)
       ========================================================================= */

    /**
     * AJAX: XÓA VĨNH VIỄN MỘT THẺ KHỎI HỆ THỐNG
     */
    public function actionAjaxDeleteCard($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // ĐÃ SỬA: Chấp nhận ID từ POST body để khớp với yêu cầu Fetch từ giao diện
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
    /**
     * AJAX: GỠ THẺ KHỎI BỘ (SET DECKID = NULL)
     * Thẻ vẫn tồn tại trong kho nhưng không còn thuộc bộ bài nào.
     */
    public function actionAjaxRemoveFromDeck($id = null) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // ĐÃ SỬA: Chấp nhận ID từ POST body để tránh lỗi tham số rỗng
        $id = $id ?? Yii::$app->request->post('id') ?? Yii::$app->request->get('id');
        $userId = Yii::$app->user->id;

        $model = Card::findOne(['cardid' => $id, 'userid' => $userId]);
        if ($model) {
            $model->deckid = null; // Trở thành thẻ tự do (kho chung)
            if ($model->save(false)) {
                return ['success' => true, 'message' => 'Đã gỡ thẻ thành công.'];
            }
        }
        return ['success' => false, 'message' => 'Lỗi: Không thể gỡ thẻ.'];
    }
    public function actionAjaxImportDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $deckId = Yii::$app->request->post('deckId');
        $userId = Yii::$app->user->id;

        // 1. Tìm bộ bài gốc
        $originalDeck = Deck::find()->where(['deckid' => $deckId])->with('cards')->one();

        if (!$originalDeck) {
            return ['success' => false, 'message' => 'Không tìm thấy bộ bài với ID: ' . $deckId];
        }

        // 2. Chặn nếu nhập bộ bài của chính mình (Tránh rác dữ liệu)
        if ($originalDeck->userid == $userId) {
            return ['success' => false, 'message' => 'Bạn không thể nhập bộ bài của chính mình.'];
        }

        // 3. Tiến hành sao chép bộ bài
        $newDeck = new Deck();
        $newDeck->name = $originalDeck->name . " (Đã nhập)";
        $newDeck->description = $originalDeck->description;
        $newDeck->userid = $userId; 

        if ($newDeck->save()) {
            // 4. Sao chép từng thẻ một (Copy toàn bộ thông tin thay vì chỉ mặt trước/sau)
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
                $newCard->save(false); // Dùng false để bỏ qua validation nếu cần thiết cho nhanh
            }
            return ['success' => true, 'message' => 'Đã nhập thành công bộ bài: ' . $originalDeck->name];
        }

        return ['success' => false, 'message' => 'Có lỗi xảy ra khi lưu dữ liệu bộ bài mới.'];
    }
    /**
     * AJAX: Cập nhật thông tin bộ thẻ (Tên/Mô tả)
     */
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

        // KIỂM TRA TRÙNG TÊN KHI ĐỔI TÊN
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
    /**
     * AJAX: Xóa toàn bộ một bộ bài
     */
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

    /**
     * AJAX: Tạo một bộ bài hoàn toàn mới
     */
   public function actionAjaxCreateDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $name = trim($data['name'] ?? 'Bộ bài mới');
        $userId = Yii::$app->user->id; 

        // KIỂM TRA TRÙNG TÊN RÕ RÀNG NGAY TỪ CONTROLLER
        $exists = Deck::findOne(['name' => $name, 'userid' => $userId]);
        if ($exists) {
            return ['success' => false, 'message' => 'Bạn đã có bộ thẻ với tên này rồi. Vui lòng chọn tên khác!'];
        }

        $model = new Deck();
        $model->name = $name;
        // Bắt buộc ép kiểu string, tránh null gây lỗi DB
        $model->description = trim($data['description'] ?? '');
        $model->userid = $userId; 
        
        try {
            if ($model->save()) {
                // Tự động tạo DeckSettings mặc định luôn để tránh lỗi các trang khác
                $setting = new DeckSettings();
                $setting->deckid = $model->deckid;
                $setting->save(false);

                return ['success' => true, 'message' => 'Tạo bộ thẻ mới thành công!'];
            }
            
            // Xử lý báo lỗi chi tiết nếu Model validate thất bại
            $errorMsg = reset($model->errors)[0] ?? 'Lỗi không xác định khi tạo bộ thẻ.';
            return ['success' => false, 'message' => 'Lỗi dữ liệu: ' . $errorMsg];
            
        } catch (\Exception $e) {
            // IN RA MÃ LỖI GỐC CỦA SQL ĐỂ DEBUG (CHỈ DÙNG KHI DEV)
            return ['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()];
        }
    }

    /**
     * AJAX: Cập nhật Profile (Họ tên, Mật khẩu, Avatar)
     */
    public function actionAjaxUpdateProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) return ['success' => false, 'message' => 'Phiên đăng nhập hết hạn.'];

        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();

        if (!empty($post['displayname'])) $user->displayname = $post['displayname'];
        if (!empty($post['password'])) $user->setPassword($post['password']);

        // Xử lý lưu ảnh từ chuỗi Base64
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
                        }
                    }
                }
            } catch (\Exception $e) { Yii::error($e->getMessage()); }
        }

        if ($user->save(false)) {
            return [
                'success' => true,
                'displayname' => $user->displayname,
                'avatarurl' => $user->avatarurl
            ];
        }
        return ['success' => false, 'message' => 'Không thể lưu thông tin.'];
    }

    /**
     * AJAX: Lưu thẻ hàng loạt từ trang thêm thẻ
     */
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

    /**
     * Hàm phụ trợ lưu bản ghi thẻ
     */
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

    /**
     * AJAX: Gắn một thẻ có sẵn vào một bộ bài khác
     */
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

    /* =========================================================================
       PHẦN 4: LUYỆN TẬP - PRACTICE (FLASHCARD STUDY) VỚI SM-2
       ========================================================================= */

    /**
     * Trang Luyện tập: Hiển thị danh sách bộ thẻ với thống kê
     */
    public function actionPractice()
    {
        $userId = Yii::$app->user->id;
        $today = date('Y-m-d');
        
        // Lấy tất cả bộ thẻ của user
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->with(['cards', 'cards.progress'])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        // Tính toán thống kê cho mỗi bộ thẻ (áp dụng quota như Anki)
        $deckStats = [];
        foreach ($decks as $deck) {
            // Lấy cài đặt bộ thẻ
            $deckSettings = DeckSettings::findOne(['deckid' => $deck->deckid]) ?: new DeckSettings();
            $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
            $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

            // Đếm đã học hôm nay (từ ReviewLog)
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

            // Tính quota có sẵn
            $newQuotaRemaining = $maxNewCards - $todayNewCount;
            $reviewQuotaRemaining = $maxReviewCards - $todayReviewCount;

            // Đếm thẻ cần ôn với quota limit
            $new = 0;
            $learning = 0;
            $review = 0;

            foreach ($deck->cards as $card) {
                $progress = $card->progress;
                if (!$progress) {
                    // Thẻ mới chưa học - check quota
                    if ($newQuotaRemaining > 0) {
                        $new++;
                        $newQuotaRemaining--;
                    }
                } else {
                    $status = $progress->status;
                    // Check due: before end of today
                    $isDue = strtotime($progress->duedate) <= strtotime($today . ' 23:59:59');
                    
                    if ($status == 0) {
                        // Thẻ mới - check quota
                        if ($isDue && $newQuotaRemaining > 0) {
                            $new++;
                            $newQuotaRemaining--;
                        }
                    } elseif ($status == 1) {
                        // Thẻ đang học - no quota limit
                        if ($isDue) {
                            $learning++;
                        }
                    } elseif ($status == 2) {
                        // Thẻ ôn tập - check quota
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

    /**
     * Trang Học bộ thẻ: Hiển thị flashcard và xử lý SM-2
     */
    public function actionStudyDeck($deckid = null)
    {
        if (!$deckid) return $this->redirect(['site/practice']);

        $userId = Yii::$app->user->id;
        $deck = Deck::findOne(['deckid' => $deckid, 'userid' => $userId]);

        if (!$deck) return $this->redirect(['site/practice']);

        // Lấy tất cả bộ thẻ của user (cho sidebar)
        $decks = Deck::find()
            ->where(['userid' => $userId])
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        // Lấy các thẻ cần ôn tập hôm nay
        $cardsToStudy = Card::find()
            ->where(['userid' => $userId, 'deckid' => $deckid])
            ->with('progress')
            ->all();

        // Lấy cài đặt bộ thẻ để áp dụng daily limit (như Anki)
        $deckSettings = DeckSettings::findOne(['deckid' => $deckid]) ?: new DeckSettings();
        $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
        $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

        // Đếm thẻ đã học hôm nay
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

        // Tính quota còn lại
        $newQuotaRemaining = $maxNewCards - $todayNewCount;
        $reviewQuotaRemaining = $maxReviewCards - $todayReviewCount;

        // Lọc thẻ cần ôn (sắp xếp ưu tiên: mới > learning > review due)
        $dueSoon = [];
        $new = [];
        $learning = [];
        $review = [];

        foreach ($cardsToStudy as $card) {
            $progress = $card->progress;
            if (!$progress) {
                $new[] = $card;
            } else {
                // Check due: thẻ phải due trước cuối hôm nay (giống vocabset)
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

        // Áp dụng daily limit: lọc các thẻ available theo quota
        $availableDue = [];
        foreach ($dueSoon as $card) {
            $progress = $card->progress;
            if ($progress && $progress->status == 2 && $reviewQuotaRemaining > 0) {
                $availableDue[] = $card;
                $reviewQuotaRemaining--;
            } elseif ($progress && $progress->status == 1) {
                // Learning card không có quota limit, luôn show
                $availableDue[] = $card;
            } elseif (!$progress && $newQuotaRemaining > 0) {
                $availableDue[] = $card;
                $newQuotaRemaining--;
            } elseif ($progress && $progress->status == 0 && $newQuotaRemaining > 0) {
                $availableDue[] = $card;
                $newQuotaRemaining--;
            }
        }

        // Additional: Filter $new cards by quota (những thẻ chưa học, chưa due)
        $availableNew = [];
        foreach ($new as $card) {
            if ($newQuotaRemaining > 0) {
                $availableNew[] = $card;
                $newQuotaRemaining--;
            }
        }

        // Lọc thẻ learning chưa due (không bị quota limit)
        $availableLearning = $learning;

        // Ưu tiên: thẻ sắp đến hạn > thẻ mới > đang học
        // CHỈ show những thẻ DUE hôm nay, chưa học (còn quota), hoặc đang học
        $priorityQueue = array_merge($availableDue, $availableNew, $availableLearning);

        // Lấy thẻ đầu tiên (hoặc redirect nếu không có)
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

    /**
     * AJAX: Lấy thẻ tiếp theo cần học
     */
    public function actionAjaxGetNextCard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $deckId = Yii::$app->request->post('deckId');
        $currentCardId = Yii::$app->request->post('currentCardId');
        $userId = Yii::$app->user->id;

        // Get deck settings for daily limits
        $deckSettings = DeckSettings::findOne(['deckid' => $deckId]) ?: new DeckSettings();
        $maxNewCards = $deckSettings->maxnewcardsperday ?? 20;
        $maxReviewCards = $deckSettings->maxreviewcardsperday ?? 200;

        // Count today's review counts (from ReviewLog where reviewdate is today)
        $today = date('Y-m-d');
        $todayReviews = ReviewLog::find()
            ->joinWith('card')
            ->where(['>=', 'DATE(reviewlogs.reviewdate)', $today])
            ->andWhere(['cards.deckid' => $deckId, 'cards.userid' => $userId])
            ->count();

        // Breakdown of today's reviews by card status
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

        // Lấy thẻ tiếp theo từ queue (tính lại từ DB)
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
                // FIX: Kiểm tra isDue để chỉ hiển thị thẻ đã đến hạn học (không hiển thị thẻ được lên lịch tương lai)
                if ($progress->status == 0) {
                    $dueSoon[] = $card; // Thẻ mới luôn hiển thị
                } elseif ($progress->status == 1 && $progress->isDue()) {
                    $learning[] = $card; // Thẻ đang learn, chỉ nếu đã đến hạn
                } elseif ($progress->status == 2 && $progress->isDue()) {
                    $dueSoon[] = $card; // Thẻ review, chỉ nếu đã đến hạn
                }
            }
        }

        // Apply daily limits: Filter due cards by daily quotas
        // Nhưng nếu user đã vào study session, không nên block bởi quota
        // Chỉ add status như bình thường
        $availableDue = $dueSoon;
        $availableNew = $new;

        // Ưu tiên: thẻ review > thẻ mới > đang học
        $priorityQueue = array_merge($availableDue, $availableNew, $learning);

        // Tìm thẻ tiếp theo
        // Nếu chỉ có 1 thẻ và nó vừa được grade, sẽ return lại nó để show tiếp
        $nextCard = null;
        $skipFirst = true;
        foreach ($priorityQueue as $card) {
            if ($card->cardid != $currentCardId) {
                $nextCard = $card;
                break;
            } elseif ($card->cardid == $currentCardId && !$skipFirst) {
                // Thẻ đó xuất hiện lần 2, có thể return nó
                $nextCard = $card;
                break;
            }
        }
        
        // Nếu không tìm thấy thẻ khác, check xem currentCard có trong queue không
        // Nếu có = hỏi có thể show lại
        if (!$nextCard) {
            foreach ($priorityQueue as $card) {
                if ($card->cardid == $currentCardId) {
                    // Thẻ hiện tại vẫn trong queue (ví dụ vừa score "Again" thành Learning)
                    $nextCard = $card;
                    break;
                }
            }
        }

        if (!$nextCard) {
            // Hết thẻ, quay lại practice
            return [
                'success' => true,
                'finished' => true,
                'message' => 'Hoàn thành tất cả thẻ trong bộ này! 🎉'
            ];
        }

        // Tìm vị trí của thẻ tiếp theo trong priorityQueue
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
                // SM2 data for JavaScript grade timing calculation
                'status' => $nextCard->progress->status ?? 0,
                'intervaldays' => $nextCard->progress->intervaldays ?? 0,
                'repetitions' => $nextCard->progress->repetitions ?? 0,
                'easefactor' => $nextCard->progress->easefactor ?? 2.5,
            ]
        ];
    }

    /**
     * AJAX: Đánh giá thẻ và cập nhật tiến độ theo SM-2
     */
    public function actionAjaxGradeCard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $cardId = Yii::$app->request->post('cardId');
        $grade = Yii::$app->request->post('grade'); // 0-4
        $userId = Yii::$app->user->id;

        // Kiểm tra thẻ tồn tại và thuộc user
        $card = Card::findOne(['cardid' => $cardId, 'userid' => $userId]);
        if (!$card) {
            return ['success' => false, 'message' => 'Thẻ không tìm thấy.'];
        }

        // Lấy hoặc tạo progress record
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

        // Lưu lịch sử đánh giá
        $reviewLog = new \app\models\ReviewLog();
        $reviewLog->cardid = $cardId;
        $reviewLog->grade = $grade;
        $reviewLog->reviewdate = date('Y-m-d H:i:s');
        $reviewLog->save(false);

        // Tính toán SM-2
        $sm2Result = SM2Helper::calculateNextReview(
            $grade,
            $progress->status,
            $progress->repetitions,
            $progress->intervaldays ?: 0,
            $progress->easefactor,
            $progress->lapses ?? 0
        );

        // Cập nhật progress
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