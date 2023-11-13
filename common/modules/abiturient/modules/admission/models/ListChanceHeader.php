<?php

namespace common\modules\abiturient\modules\admission\models;
use yii\behaviors\TimestampBehavior;

class ListChanceHeader extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%chance_list}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    public function rules()
    {
        return [
            [['filename'], 'unique'],
        ];
    }
    
    public function getRows(){
        
         return $this->hasMany(ListChanceRow::class, ['chance_list_id'=>'id'])->orderBy(['chance_list_rows.row_number'=>SORT_ASC]);
    }
}


