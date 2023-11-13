<?php

namespace common\models\dictionary;

class EducationInfo extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%dictionary_education_info}}';
    }

    


    public function rules()
    {
        return [
            [['education_type_code', 'document_type_code'], 'required'],
            [['education_type_code','document_type_code'], 'string', 'max' => 100],
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'education_type_code' => 'Вид образования',
            'document_type_code' => 'Вид документа об образовании',
        ];
    }
}