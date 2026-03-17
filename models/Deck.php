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
     * Tên bảng trong database (viết thường như bạn đã tạo)
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
            [['description'], 'string'],
            [['createdat'], 'safe'],
            [['name'], 'string', 'max' => 255],
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