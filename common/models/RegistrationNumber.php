<?php
namespace common\models;




class RegistrationNumber extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return '{{%user_regnumber}}';
    }
    
    


    public function rules()
    {
        return [
            [['user_id'],'integer'],
            [['registration_number'], 'string', 'max' => 100],
            [['user_id', 'registration_number'], 'required'],
            ['registration_number','unique'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'user_id' => 'Пользователь',
            'registration_number' => 'Регномер',
           
        ];
    } 
    
}