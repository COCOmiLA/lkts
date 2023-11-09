<?php

namespace common\modules\abiturient\modules\admission\models;
use yii\behaviors\TimestampBehavior;

class ListCompetitionRow extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return '{{%competition_list_rows}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    public function getHeader(){
        
         return $this->hasOne(ListCompetitionHeader::class, ['id'=>'competition_list_id']);
    }
   
}
