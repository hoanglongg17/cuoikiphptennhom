<?php

namespace app\models;

use yii\db\ActiveRecord;

class CardProgress extends ActiveRecord
{
    public $lapses = 0;

    public static function tableName()
    {
        return 'cardprogress';
    }

    public function rules()
    {
        return [
            [['cardid'], 'required'],
            [['cardid', 'status', 'repetitions', 'lapses'], 'integer'],
            [['intervaldays', 'easefactor'], 'number'],
            [['duedate'], 'safe'],
            [['cardid'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'progressid' => 'ID',
            'cardid' => 'Thẻ',
            'status' => 'Trạng thái',
            'duedate' => 'Ngày học tiếp',
            'intervaldays' => 'Khoảng cách (ngày)',
            'easefactor' => 'Hệ số dễ',
            'repetitions' => 'Số lần học',
        ];
    }

    
    public function getCard()
    {
        return $this->hasOne(Card::class, ['cardid' => 'cardid']);
    }

    
    public function isDue()
    {
        return strtotime($this->duedate) <= strtotime('now');
    }

    
    public function getStatusLabel()
    {
        $labels = [
            0 => 'Mới',
            1 => 'Đang học',
            2 => 'Ôn tập',
        ];
        return $labels[$this->status] ?? 'Không xác định';
    }
}