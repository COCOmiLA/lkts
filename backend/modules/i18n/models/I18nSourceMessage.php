<?php

namespace backend\modules\i18n\models;

use Yii;










class I18nSourceMessage extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%i18n_source_message}}';
    }

    


    public function rules()
    {
        return [
            [['message'], 'string'],
            [['category'], 'string', 'max' => 32]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'category' => Yii::t('backend', 'Категория'),
            'message' => Yii::t('backend', 'Сообщение'),
        ];
    }

    


    public function getI18nMessages()
    {
        return $this->hasMany(I18nMessage::class, ['id' => 'id']);
    }
}
