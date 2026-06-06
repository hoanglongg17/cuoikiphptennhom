<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;

class ChatbotController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'send-message'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Hiển thị trang chatbot chính
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * API endpoint xử lý tin nhắn từ chatbot
     * POST /chatbot/send-message
     * 
     * @param string $message Nội dung tin nhắn từ người dùng
     * @return array
     */
    public function actionSendMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->method !== 'POST') {
            return [
                'success' => false,
                'error' => 'Method not allowed',
            ];
        }

        $message = Yii::$app->request->post('message', '');
        $message = trim($message);

        if (empty($message)) {
            return [
                'success' => false,
                'error' => 'Tin nhắn không được để trống',
            ];
        }

        try {
            $reply = $this->generateReplyWithGemini($message);

            return [
                'success' => true,
                'reply' => $reply,
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            Yii::error('Chatbot error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi xử lý tin nhắn: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Tạo phản hồi từ Gemini AI qua REST API
     */
    private function generateReplyWithGemini($userMessage)
    {
        $apiKey = Yii::$app->params['geminiApiKey'];
        $modelName = Yii::$app->params['geminiModel'];

        if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            throw new \Exception('Gemini API key chưa được cấu hình. Vui lòng thêm GEMINI_API_KEY');
        }

        try {
            $vocabularyContext = $this->getVocabularyContext();

            $systemPrompt = $this->buildSystemPrompt($vocabularyContext);

            $response = $this->callGeminiRestAPI($apiKey, $modelName, $systemPrompt, $userMessage);

            return $response;
        } catch (\Exception $e) {
            Yii::error('Gemini API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy context dữ liệu từ vựng & thông tin tính năng
     */
    private function getVocabularyContext()
    {
        $context = <<<CONTEXT
=== THÔNG TIN CHI TIẾT VỀ NỀN TẢNG ANDI ===

ANDI là nền tảng học ngoại ngữ toàn diện với focus vào học từ vựng bằng Flashcard thông minh.

=== PHẦN 1: BA LOẠI THẺ HỌC TỪ VỰNG ===

A. THẺ THƯỜNG (Regular Card):
   - Mặt trước (câu hỏi): Tiếng Anh
   - Mặt sau (đáp án): Tiếng Việt
   - Cách hoạt động:
     * Người dùng nhìn thấy từ tiếng Anh trên mặt trước thẻ
     * Suy nghĩ về ý nghĩa
     * Bấm để lật thẻ xem đáp án tiếng Việt
     * Đánh dấu "Biết" hoặc "Chưa biết" để hệ thống ghi nhớ
   - Mục đích: Giúp học sinh hiểu từ tiếng Anh ➜ Tiếng Việt (chiều học tự nhiên, dễ nhất)
   - Phù hợp cho: Người mới bắt đầu, muốn học dễ trước

B. THẺ ĐẢO NGƯỢC (Reversed Card):
   - Mặt trước (câu hỏi): Tiếng Việt
   - Mặt sau (đáp án): Tiếng Anh
   - Cách hoạt động:
     * Người dùng nhìn thấy từ tiếng Việt trên mặt trước thẻ
     * Suy nghĩ và cố gắng nhớ cách nói tiếng Anh
     * Bấm để lật thẻ xem đáp án tiếng Anh
     * Đánh dấu kết quả để hệ thống ghi nhớ
   - Mục đích: Giúp học sinh tái tạo từ tiếng Việt ➜ Tiếng Anh (chiều học nâng cao, khó hơn)
   - Phù hợp cho: Người muốn nói/viết tiếng Anh, muốn chủ động ghi nhớ

C. THẺ NHẬP LIỆU (Input Card / Typing Card):
   - Mặt trước (câu hỏi): Tiếng Anh
   - Yêu cầu: Người dùng phải nhập tiếng Việt vào ô text box (không phải chọn đáp án)
   - Cách hoạt động:
     * Hệ thống hiển thị từ tiếng Anh
     * Người dùng gõ tiếng Việt vào ô text box
     * Bấm "Kiểm tra" hoặc Enter để nộp câu trả lời
     * Hệ thống so sánh với đáp án (hỗ trợ phân tích ngữ pháp)
     * Hiển thị "✓ Đúng" hoặc "✗ Sai" và điểm số
   - Mục đích: Kiểm tra chặt chẽ khả năng tái tạo từ vựng (chế độ test/exam)
   - Phù hợp cho: Kiểm tra trước kỳ thi, muốn đánh giá thực lực

=== PHẦN 2: TÍNH NĂNG LUYỆN TẬP & BỘ THẺ ===

A. BỘ THẺ (DECK):
   - Là tập hợp nhiều thẻ từ vựng được nhóm lại theo chủ đề
   - Người dùng có thể:
     * Tạo bộ thẻ mới từ đầu
     * Quản lý (chỉnh sửa, xóa) bộ thẻ của mình
     * Chia sẻ bộ thẻ với bạn bè
     * Xem thống kê tiến độ học từng bộ thẻ
     * Tìm kiếm bộ thẻ công khai của người khác
   - Mỗi bộ thẻ chứa: Tiêu đề, mô tả, danh mục, nhiều thẻ từ vựng

B. TỪ VỰNG (VOCABULARY CARD):
   - Là các thẻ riêng lẻ trong một bộ thẻ
   - Mỗi thẻ gồm:
     * Tiếng Anh
     * Tiếng Việt
     * Phiên âm (nếu có)
     * Ví dụ câu (nếu có)
   - Người dùng có thể: Thêm, sửa, xóa, sao chép từ vựng

C. LỘ TRÌNH HỌC CÁ NHÂN HÓA:
   - Hệ thống Spaced Repetition (SM2):
     * Tự động luyện lại những từ khó
     * Theo dõi tần suất gặp từ
     * Điều chỉnh độ khó theo khả năng người học
   - Thống kê chi tiết:
     * Số từ đã biết / chưa biết
     * Tỷ lệ thành công
     * Thời gian học
     * Tiến độ hàng ngày

=== PHẦN 3: TÍNH NĂNG LUYỆN TẬP ===

A. MỘT BỘ THẺ CÓ CÁC MODE LUYỆN TẬP:
   - Mode "Thẻ Thường": Học từ tiếng Anh ➜ Tiếng Việt (dễ)
   - Mode "Thẻ Đảo Ngược": Luyện tái tạo Tiếng Anh từ Tiếng Việt (khó hơn)
   - Mode "Thẻ Nhập Liệu": Kiểm tra bằng gõ tiếng Việt (chuẩn nhất, như thi)
   - Mode "Kiểm Tra": Làm bài kiểm tra để đánh giá trình độ

B. CÁCH LUYỆN TẬP:
   1. Chọn bộ thẻ muốn luyện
   2. Chọn mode luyện tập
   3. Luyện từng thẻ một
   4. Hệ thống tự động luyện lại những từ khó
   5. Xem kết quả & thống kê

=== PHẦN 4: BLOG - CHIA SẺ & CỘNG ĐỒNG ===

A. 6 TÍNH NĂNG BLOG CHÍNH:

1️⃣ TÌM KIẾM BÀI VIẾT:
   - Tìm kiếm theo tiêu đề, nội dung, tác giả
   - URL: /blog/search?q=từ_khóa
   - Kết quả hiển thị: Bài viết liên quan, số lượng views, likes

2️⃣ DANH MỤC & PHÂN LOẠI:
   - Mỗi bài viết được gán vào một danh mục (PHP, JavaScript, Học Tập, v.v.)
   - Xem tất cả bài viết của một danh mục: /blog/category/slug
   - Giúp tìm kiếm bài liên quan dễ dàng

3️⃣ NHÃN (TAGS):
   - Mỗi bài viết có nhiều nhãn (ví dụ: "Mẹo Học", "TOEIC", "Luyện Tập")
   - Xem bài viết cùng nhãn: /blog/tag/slug
   - Giúp phân loại chi tiết hơn

4️⃣ ĐÁNH GIÁ & THÍCH BÀI VIẾT:
   - Người dùng có thể bấm ❤️ để thích bài
   - Xem số likes (❤️) của bài viết
   - AJAX: Không cần refresh trang
   - Giúp tác giả biết bài viết có hữu ích không

5️⃣ BÌNH LUẬN LỒNG (NESTED COMMENTS):
   - Thảo luận dưới bài viết
   - Trả lời bình luận của người khác (nested/con)
   - Admin có thể:
     * Duyệt bình luận trước khi xuất bản
     * Từ chối bình luận không phù hợp
     * Đánh dấu spam
     * Xóa bình luận
   - Người dùng nhận thông báo khi có trả lời

6️⃣ THÔNG BÁO EMAIL:
   - Tác giả được thông báo khi có:
     * Bài viết được xuất bản/ẩn
     * Bài viết bị yêu cầu chỉnh sửa
     * Bình luận mới dưới bài
   - Hệ thống gửi email định kỳ qua console command: php yii send-notifications

B. ĐĂNG BÀI BLOG - 5 BƯỚC:
   Bước 1: Truy cập mục "Blog" trong sidebar menu
   Bước 2: Click nút "Viết bài viết mới" hoặc "Tạo bài"
   Bước 3: Nhập thông tin:
      - Tiêu đề bài viết (bắt buộc)
      - Nội dung chi tiết (hỗ trợ Markdown)
      - Chọn danh mục (bắt buộc)
      - Chọn nhãn/tags (tùy chọn)
   Bước 4: Preview bài viết để kiểm tra
   Bước 5: Ấn nút "Đăng bài viết" hoặc "Lưu nháp"
   
   Lưu ý:
   - Bài viết của user thường phải được duyệt trước khi công khai
   - Admin có thể xuất bản ngay
   - Bạn có thể chỉnh sửa bài viết của mình sau khi đã đăng
   - Bài viết phải tuân theo tiêu chuẩn cộng đồng (không spam, không quảng cáo)


=== PHẦN 5: TÍNH NĂNG NGƯỜI DÙNG ===

A. ĐĂNG KÝ & ĐĂNG NHẬP:
   - Đăng ký tài khoản mới
   - Đăng nhập bằng email
   - Đặt lại mật khẩu
   - Đăng xuất

B. HỒ SƠ NGƯỜI DÙNG:
   - Xem/Chỉnh sửa thông tin cá nhân
   - Đổi ảnh đại diện
   - Đổi mật khẩu
   - Theo dõi tiến độ học

C. BẢNG ĐIỂM & THỐNG KÊ:
   - Thống kê từng bộ thẻ
   - Lịch sử luyện tập
   - Điểm số, tỷ lệ thành công
   - Xu hướng tiến độ


=== PHẦN 6: CÁC MƠDE LUYỆN TẬP CHI TIẾT ===

A. MODE "THẺ THƯỜNG" (Reading Comprehension):
   - Hiển thị: Từ tiếng Anh
   - Người học: Suy nghĩ ý nghĩa
   - Bấm thẻ: Lật xem tiếng Việt
   - Xác nhận: Bấm "Biết" hoặc "Chưa biết"

B. MODE "THẺ ĐẢO NGƯỢC" (Recall):
   - Hiển thị: Từ tiếng Việt
   - Người học: Cố gắng nhớ tiếng Anh
   - Bấm thẻ: Lật xem đáp án tiếng Anh
   - Xác nhận: Bấm "Biết" hoặc "Chưa biết"

C. MODE "THẺ NHẬP LIỆU" (Typing Test):
   - Hiển thị: Từ tiếng Anh
   - Người học: Gõ tiếng Việt vào ô text
   - Kiểm tra: Bấm Enter hoặc nút "Kiểm tra"
   - Kết quả: Đúng/Sai, điểm số, phân tích

=== PHẦN 7: HỆ THỐNG SPACED REPETITION ===

- Tự động luyện lại những từ khó theo thời gian
- Công thức SM-2 (Supermemo Algorithm)
- Các từ "dễ" được luyện lại ít, từ "khó" luyện nhiều
- Tối ưu hóa thời gian học & ghi nhớ lâu dài

=== PHẦN 8: GIỚI THIỆU ANDI ===

ANDI là nền tảng học ngoại ngữ toàn diện với focus vào:
✓ Học từ vựng hiệu quả bằng phương pháp Flashcard
✓ Hệ thống Spaced Repetition khoa học
✓ Cộng đồng tích cực chia sẻ kiến thức
✓ Thống kê chi tiết để theo dõi tiến độ
✓ Ba loại thẻ phù hợp với từng mục đích học
✓ Admin Panel quản lý nội dung blog
✓ Chatbot AI hỗ trợ 24/7

Tính năng nổi bật:
✓ Giao diện thân thiện, dễ sử dụng
✓ Ba loại thẻ học phù hợp với từng mục đích
✓ Thống kê chi tiết về tiến độ học
✓ Blog cộng đồng với 6 tính năng mạnh mẽ
✓ Hỗ trợ nhiều ngôn ngữ
✓ Chatbot AI hỗ trợ 24/7
✓ Phương pháp học khoa học (Spaced Repetition)

CONTEXT;

        return $context;
    }

    /**
     * Build system prompt cho Gemini
     */
    private function buildSystemPrompt($vocabularyContext)
    {
        return <<<PROMPT
Bạn là trợ lý hỗ trợ của nền tảng học ngoại ngữ "ANDI". Bạn là một cố vấn tư vấn thân thiện, am hiểu sâu về học từ vựng và cả nền tảng.

Nhiệm vụ của bạn:
1. Giải thích về các loại thẻ trên ANDI (Vocabulary Card, Deck, Flashcard)
2. Hướng dẫn người dùng cách sử dụng các tính năng của ANDI
3. Cung cấp lời khuyên về cách học từ vựng hiệu quả
4. Trả lời câu hỏi về cách đăng bài blog
5. Giới thiệu về nền tảng và các tính năng nổi bật
6. Hỗ trợ các vấn đề thường gặp khi sử dụng ANDI

$vocabularyContext

Hướng dẫn trả lời:
- Trả lời bằng tiếng Việt
- Ngắn gọn nhưng đầy đủ thông tin (2-3 đoạn văn)
- **QUAN TRỌNG: Chia đoạn bằng cách sử dụng hai dòng trống (\n\n) giữa các đoạn văn để dễ đọc**
- Mỗi đoạn nên có 2-4 câu
- Trả lời đúng trọng tâm, đừng đi quá sâu vào các vấn đề phụ nếu không cần thiết
- Sử dụng ví dụ cụ thể khi cần thiết
- Nếu người dùng hỏi về tính năng, hãy giải thích chi tiết cách sử dụng
- Luôn hỗ trợ tích cực và khuyến khích người dùng học tập
- Không sử dụng Markdown formatting, ** hoặc __ hay ```
- Tự nhiên, thân thiện, giống như một "cố vấn" thực sự của ANDI
- Đôi khi sử dụng emoji phù hợp để tăng sự thân thiện (chỉ 1-2 emoji)
PROMPT;
    }

    /**
     * Gọi Gemini API qua REST endpoint
     */
    private function callGeminiRestAPI($apiKey, $modelName, $systemPrompt, $userMessage)
    {
        $model = str_replace('models/', '', $modelName);
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $fullPrompt = "{$systemPrompt}\n\nCâu hỏi từ khách hàng: {$userMessage}\n\nTrợ lý:";
        
        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $fullPrompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 1.0,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL error: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorMsg = "HTTP {$httpCode}";
            $decodedResponse = json_decode($response, true);
            if (isset($decodedResponse['error']['message'])) {
                $errorMsg .= ': ' . $decodedResponse['error']['message'];
            }
            throw new \Exception('Gemini API error: ' . $errorMsg);
        }

        $decodedResponse = json_decode($response, true);

        if (!isset($decodedResponse['candidates']) || empty($decodedResponse['candidates'])) {
            throw new \Exception('Không nhận được phản hồi từ Gemini API');
        }

        $candidate = $decodedResponse['candidates'][0];
        if (!isset($candidate['content']['parts']) || empty($candidate['content']['parts'])) {
            throw new \Exception('Phản hồi từ Gemini API không hợp lệ');
        }

        $text = $candidate['content']['parts'][0]['text'];

        $text = str_replace(['**', '__', '```', '***'], '', $text);
        $text = trim($text);

        return !empty($text) ? $text : 'Xin lỗi, tôi không thể trả lời câu hỏi này lúc này.';
    }
}
