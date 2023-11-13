<?php

namespace common\models\dictionary;

use yii\behaviors\TimestampBehavior;





class DocumentAbiturientType extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%dictionary_document_abiturient_type}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['id_pk', 'document_set_code', 'document_type_code', 'number_document', 'scan_required'], 'safe'],
            [['id_pk', 'document_set_code', 'document_type_code',], 'string', 'max' => 255],
            [['number_document', 'scan_required'], 'boolean'],
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'document_set_code' => 'document_set_code',
            'document_type_code' => 'document_type_code',
            'number_document' => 'number_document',
            'scan_required' => 'scan_required',
        ];
    }
}
