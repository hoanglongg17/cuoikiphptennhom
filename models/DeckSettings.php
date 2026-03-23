<?php

namespace app\models;

use yii\db\ActiveRecord;

class DeckSettings extends ActiveRecord
{
    public static function tableName()
    {
        return 'decksettings';
    }

    public function rules()
    {
        return [
            [['deckid'], 'required'],
            [['deckid', 'maxnewcardsperday', 'maxreviewcardsperday'], 'integer'],
            [['deckid'], 'unique'],
            [['deckid'], 'exist', 'skipOnError' => true, 'targetClass' => Deck::class, 'targetAttribute' => ['deckid' => 'deckid']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'deckid' => 'Bộ thẻ',
            'maxnewcardsperday' => 'Số thẻ mới tối đa/ngày',
            'maxreviewcardsperday' => 'Số thẻ ôn tập tối đa/ngày',
        ];
    }

    /**
     * Quan hệ: Một cài đặt thuộc về một bộ thẻ
     */
    public function getDeck()
    {
        return $this->hasOne(Deck::class, ['deckid' => 'deckid']);
    }
}
