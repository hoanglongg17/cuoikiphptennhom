<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


class Card extends ActiveRecord
{
    
    public static function tableName()
    {
        return 'cards';
    }

 
    public function rules()
    {
        return [
            [['deckid', 'frontcontent', 'backcontent'], 'required'],
            [['deckid'], 'integer'],
            [['frontcontent', 'backcontent', 'examplesentence'], 'string'],
            [['createdat'], 'safe'],
            [['pronunciation', 'tags'], 'string', 'max' => 255],
            [['audiourl'], 'string', 'max' => 500],
            [['deckid'], 'exist', 'skipOnError' => true, 'targetClass' => Deck::class, 'targetAttribute' => ['deckid' => 'deckid']],
        ];
    }

    public function getProgress()
    {
        return $this->hasOne(CardProgress::class, ['cardid' => 'cardid']);
    }

    public function getDeck()
    {
        return $this->hasOne(Deck::class, ['deckid' => 'deckid']);
    }
}