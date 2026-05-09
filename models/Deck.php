<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Đây là lớp Model cho bảng "decks".
 *
 * @property int $deckid
 * @property int $userid
 * @property string $name
 * @property string|null $description
 * @property string|null $createdat
 *
 * @property Card[] $cards
 */
class Deck extends ActiveRecord
{
    /**
     * Tên bảng trong database
     */
    public static function tableName()
    {
        return 'decks';
    }

    /**
     * Quy tắc kiểm tra dữ liệu
     */
    public function rules()
    {
        return [
            [['userid', 'name'], 'required'],
            [['userid'], 'integer'],
            [['description'], 'string'], // ĐÃ XÓA cover_image
            [['createdat'], 'safe'],     // ĐÃ ĐỔI THÀNH createdat (không có gạch dưới)
            [['name'], 'string', 'max' => 255],
            
            // Sửa lại targetAttribute của User cho khớp với cột userid trong bảng users
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
            
            // CHẶN TRÙNG TÊN BỘ THẺ THEO USER
            [
                ['name'], 
                'unique', 
                'targetAttribute' => ['name', 'userid'], 
                'message' => 'Bạn đã có bộ thẻ với tên này rồi. Vui lòng chọn tên khác!'
            ],
        ];
    }

    /**
     * Thiết lập quan hệ: Một bộ thẻ (Deck) có nhiều Thẻ (Cards)
     */
    public function getCards()
    {
        return $this->hasMany(Card::class, ['deckid' => 'deckid']);
    }
}