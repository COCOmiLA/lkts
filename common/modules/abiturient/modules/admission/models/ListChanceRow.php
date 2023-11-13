<?php

namespace common\modules\abiturient\modules\admission\models;
use yii\behaviors\TimestampBehavior;

class ListChanceRow extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return '{{%chance_list_rows}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    public function getHeader(){
        
         return $this->hasOne(ListChanceHeader::class, ['id'=>'chance_list_id']);
    }
   
}
