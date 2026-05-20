<?php

namespace app\models;

use yii\db\ActiveRecord;

class ReviewLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'reviewlogs';
    }

    public function rules()
    {
        return [
            [['cardid', 'grade'], 'required'],
            [['cardid', 'grade', 'durationms'], 'integer'],
            [['reviewdate'], 'safe'],
            [['cardid'], 'exist', 'skipOnError' => true, 'targetClass' => Card::class, 'targetAttribute' => ['cardid' => 'cardid']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'logid' => 'ID',
            'cardid' => 'Thẻ',
            'grade' => 'Đánh giá',
            'reviewdate' => 'Ngày ôn tập',
            'durationms' => 'Thời gian (ms)',
        ];
    }

    
    public function getCard()
    {
        return $this->hasOne(Card::class, ['cardid' => 'cardid']);
    }

    
    public function getCardProgress()
    {
        return $this->hasOne(CardProgress::class, ['cardid' => 'cardid']);
    }
}
