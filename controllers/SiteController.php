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


class SiteController extends Controller
{
    public $layout = 'landing';

    /**
     * Cấu hình quyền truy cập
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'dashboard', 'signup', 'login'],
                'rules' => [
                    [
                        'actions' => ['dashboard', 'logout'],
                        'allow' => true,
                        'roles' => ['@'], // Chỉ cho phép người đã đăng nhập
                    ],
                    [
                        'actions' => ['signup', 'login'],
                        'allow' => true,
                        'roles' => ['?'], // Chỉ cho phép khách (chưa đăng nhập)
                    ],
                ],
                // Xử lý khi người dùng cố tình truy cập trang bị cấm
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->response->redirect(['site/login']);
                    } else {
                        return Yii::$app->response->redirect(['site/dashboard']);
                    }
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Các Action mở rộng
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

    public function actionIndex()
    {
        $this->layout = 'landing';
        return $this->render('index');
    }

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
    
public function actionLogout()
{
    Yii::$app->user->logout();
    return $this->redirect(['site/login']); // Thoát xong đưa về trang Login
}

    
     public function actionVocabset()
    {
        // Vì là bản demo chưa có login, ta lấy TẤT CẢ bộ thẻ để hiển thị
        $decks = Deck::find()
            ->with(['cards', 'cards.progress']) // Tải trước dữ liệu thẻ để Pop-up mượt mà
            ->orderBy(['createdat' => SORT_DESC])
            ->all();

        return $this->render('vocabset', [
            'decks' => $decks,
        ]);
    }

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

    public function actionAjaxUpdateProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Phiên đăng nhập hết hạn.'];
        }

        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();

        // 1. Cập nhật tên hiển thị
        if (!empty($post['displayname'])) {
            $user->displayname = $post['displayname'];
        }

        // 2. CẬP NHẬT: Mã hóa mật khẩu mới nếu có thay đổi
        if (!empty($post['password'])) {
            $user->setPassword($post['password']);
        }

        // 3. Xử lý lưu ảnh đại diện (giữ nguyên logic bạn đã có)
        if (!empty($post['avatar_base64'])) {
            try {
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
                if (!is_dir($uploadPath)) {
                    FileHelper::createDirectory($uploadPath);
                }

                if (preg_match('/^data:image\/(\w+);base64,/', $post['avatar_base64'], $type)) {
                    $data = substr($post['avatar_base64'], strpos($post['avatar_base64'], ',') + 1);
                    $type = strtolower($type[1]); 

                    $data = base64_decode($data);
                    if ($data !== false) {
                        $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $type;
                        $filePath = $uploadPath . '/' . $fileName;

                        if (file_put_contents($filePath, $data)) {
                            $user->avatarurl = Yii::getAlias('@web/uploads/avatars/') . $fileName;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log lỗi nếu cần
            }
        }

        if ($user->save(false)) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Không thể lưu thông tin.'];
    }

     public function actionAjaxCreateDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        
        $model = new Deck();
        $model->name = $data['name'];
        $model->description = $data['description'];
        $model->userid = 1; // Mặc định ID = 1 cho bản demo

        if ($model->save()) {
            return ['success' => true, 'message' => 'Đã tạo bộ thẻ thành công!'];
        }
        return ['success' => false, 'errors' => $model->errors];
    }
    public function actionVocabulary($deck_id = null)
    {
        // 1. Lấy danh sách tất cả bộ thẻ để làm Bộ lọc (Filter)
        $decks = Deck::find()->orderBy(['createdat' => SORT_DESC])->all();

        // 2. Truy vấn danh sách thẻ
        $query = Card::find()->with('progress')->orderBy(['createdat' => SORT_DESC]);

        // Nếu người dùng có chọn bộ lọc
        if ($deck_id) {
            $query->andWhere(['deckid' => $deck_id]);
        }

        $cards = $query->all();

        // 3. Tính toán thống kê
        $total = count($cards);
        $memorized = 0;
        $learning = 0;

        foreach ($cards as $card) {
            // Giả định: status = 2 là đã thuộc (Ôn tập), 0 và 1 là chưa thuộc
            $status = $card->progress ? $card->progress->status : 0;
            if ($status == 2) {
                $memorized++;
            } else {
                $learning++;
            }
        }

        $percent = $total > 0 ? round(($memorized / $total) * 100) : 0;

        return $this->render('vocabulary', [
            'decks' => $decks,
            'cards' => $cards,
            'currentDeckId' => $deck_id,
            'stats' => [
                'total' => $total,
                'memorized' => $memorized,
                'learning' => $learning,
                'percent' => $percent
            ]
        ]);
    }

    public function actionAjaxImportDeck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $deckId = Yii::$app->request->post('deckId');

        // 1. Tìm bộ bài gốc dựa trên ID
        $originalDeck = Deck::find()->where(['deckid' => $deckId])->with('cards')->one();

        if (!$originalDeck) {
            return ['success' => false, 'message' => 'Không tìm thấy bộ bài với ID: ' . $deckId];
        }

        // 2. Tạo bản sao bộ bài mới cho User hiện tại (Demo UserID = 1)
        $newDeck = new Deck();
        $newDeck->name = $originalDeck->name . " (Đã nhập)";
        $newDeck->description = $originalDeck->description;
        $newDeck->userid = 1; 

        if ($newDeck->save()) {
            // 3. Sao chép toàn bộ thẻ từ bộ bài gốc sang bộ bài mới
            foreach ($originalDeck->cards as $card) {
                $newCard = new Card();
                $newCard->deckid = $newDeck->deckid;
                $newCard->frontcontent = $card->frontcontent;
                $newCard->backcontent = $card->backcontent;
                $newCard->pronunciation = $card->pronunciation;
                $newCard->examplesentence = $card->examplesentence;
                $newCard->tags = $card->tags;
                $newCard->save();
            }
            return ['success' => true, 'message' => 'Đã nhập thành công bộ bài: ' . $originalDeck->name];
        }

        return ['success' => false, 'message' => 'Có lỗi xảy ra khi lưu dữ liệu.'];
    }

    /**
     * AJAX: Cập nhật thông tin bộ thẻ
     */
    public function actionAjaxUpdateDeck($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Deck::findOne($id);
        if ($model) {
            $data = Yii::$app->request->post();
            $model->name = $data['name'];
            $model->description = $data['description'];
            if ($model->save()) {
                return ['success' => true];
            }
        }
        return ['success' => false];
    }

    /**
     * AJAX: Xóa bộ thẻ
     */
    public function actionAjaxDeleteDeck($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Deck::findOne($id);
        if ($model && $model->delete()) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    /**
     * AJAX: Xóa thẻ khỏi bộ
     */
    public function actionAjaxDeleteCard($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Card::findOne($id);
        if ($model && $model->delete()) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    public function actionAjaxSaveBatchCards()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request->post();
        
        $deckId = $request['deckId'] ?? null;
        // 1. Bắt giá trị cardType từ JavaScript gửi lên (Mặc định là 1 nếu không có)
        $cardType = $request['cardType'] ?? 1; 
        $cardsData = json_decode($request['cards'], true);

        if (!$deckId || empty($cardsData)) {
            return ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];
        }

        // Tên nhãn để hiển thị đẹp ra giao diện Từ vựng
        $typeLabel = 'Cơ bản';
        if ($cardType == 2) $typeLabel = 'Đảo ngược';
        if ($cardType == 3) $typeLabel = 'Nhập liệu';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($cardsData as $data) {
                $userTags = isset($data['tags']) && trim($data['tags']) !== '' ? trim($data['tags']) : '';
                $data['tags'] = $userTags !== '' ? $typeLabel . ', ' . $userTags : $typeLabel;
                
                // 2. PHẢI truyền biến $cardType vào hàm saveCardInstance
                $this->saveCardInstance($deckId, $data, $cardType);

                // Nếu là thẻ Đảo ngược, tạo thêm một mặt ngược lại
                if ($cardType == 2) {
                    $reversed = $data;
                    $reversed['front'] = $data['back'];
                    $reversed['back'] = $data['front'];
                    // Thẻ lộn ngược này cũng có kiểu là 2
                    $this->saveCardInstance($deckId, $reversed, $cardType);
                }
            }
            $transaction->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Hàm phụ trợ lưu một bản ghi thẻ
     */
    private function saveCardInstance($deckId, $data, $type) {
        $model = new Card();
        $model->deckid = $deckId;
        
        // 3. GÁN KIỂU THẺ VÀO DATABASE Ở ĐÂY
        $model->cardtype = $type; 
        
        $model->frontcontent = $data['front'];
        $model->backcontent = $data['back'];
        $model->pronunciation = $data['pronunciation'] ?? '';
        $model->examplesentence = $data['example'] ?? '';
        $model->tags = $data['tags'] ?? '';
        $model->createdat = date('Y-m-d H:i:s');
        
        if (!$model->save()) {
            throw new \Exception("Không thể lưu thẻ: " . json_encode($model->errors));
        }
    }

    public function actionAjaxRemoveFromDeck($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = Card::findOne($id);
        if ($model) {
            $model->deckid = null; // Trở thành thẻ tự do (kho chung)
            if ($model->save(false)) {
                return ['success' => true];
            }
        }
        return ['success' => false];
    }

    /**
     * AJAX: Thêm thẻ có sẵn vào một bộ bài
     */
    public function actionAjaxAssignCardToDeck() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $cardId = Yii::$app->request->post('cardId');
        $newDeckId = Yii::$app->request->post('deckId');

        $card = Card::findOne($cardId);
        if (!$card || !$newDeckId) return ['success' => false, 'message' => 'Dữ liệu không hợp lệ.'];

        if ($card->deckid === null) {
            // Nếu thẻ đang ở kho chung, đẩy luôn vào bộ bài
            $card->deckid = $newDeckId;
            $card->save(false);
        } else {
            // Nếu thẻ đang ở bộ khác, tạo bản sao sang bộ mới để tiến độ học độc lập
            $newCard = new Card();
            $newCard->attributes = $card->attributes; // Copy mọi thứ
            $newCard->cardid = null; // Tạo ID mới
            $newCard->deckid = $newDeckId;
            $newCard->createdat = date('Y-m-d H:i:s');
            $newCard->save(false);
        }
        return ['success' => true];
    }
}
