<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class Deck extends ActiveRecord
{
    
    public static function tableName()
    {
        return 'decks';
    }

    
    public function rules()
    {
        return [
            [['userid', 'name'], 'required'],
            [['userid'], 'integer'],
            [['description'], 'string'], 
            [['createdat'], 'safe'],     
            [['name'], 'string', 'max' => 255],
            
            
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
            
            
            [
                ['name'], 
                'unique', 
                'targetAttribute' => ['name', 'userid'], 
                'message' => 'Bạn đã có bộ thẻ với tên này rồi. Vui lòng chọn tên khác!'
            ],
        ];
    }

    
    public function getCards()
    {
        return $this->hasMany(Card::class, ['deckid' => 'deckid']);
    }
}