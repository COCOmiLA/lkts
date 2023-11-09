<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;







class KeyStorageItem extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%key_storage_item}}';
    }

    public function behaviors()
    {
        return [
            [
              'class' => TimestampBehavior::class,
            ],
        ];
    }

    


    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['key'], 'string', 'max'=>128],
            [['value', 'comment'], 'safe'],
            [['key'], 'unique']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'key' => Yii::t('common', 'Ключ'),
            'value' => Yii::t('common', 'Значение'),
            'comment' => Yii::t('common', 'Комментарий'),
        ];
    }
}
