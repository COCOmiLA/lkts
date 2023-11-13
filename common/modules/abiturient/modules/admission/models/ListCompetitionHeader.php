<?php

namespace common\modules\abiturient\modules\admission\models;
use yii\behaviors\TimestampBehavior;

class ListCompetitionHeader extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return '{{%competition_list}}';
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
        
         return $this->hasMany(ListCompetitionRow::class, ['competition_list_id'=>'id'])->orderBy(['competition_list_rows.row_number'=>SORT_ASC]);
    }
  
    public static function getQualificationText($code){
        switch((int)$code){
            case(0):
                return 'Специалист/бакалавр';
            case(1):
                return 'Магистр';
            case(2):
                return 'Аспирант';   
            default:
                return null;
        }
    }
}


