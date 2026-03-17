<?php

namespace app\models;

use yii\db\ActiveRecord;

class CardProgress extends ActiveRecord
{
    public static function tableName()
    {
        return 'cardprogress';
    }

    public function rules()
    {
        return [
            [['cardid'], 'required'],
            [['cardid', 'status', 'repetitions'], 'integer'],
            [['intervaldays', 'easefactor'], 'number'],
            [['duedate'], 'safe'],
            [['cardid'], 'unique'],
        ];
    }
}