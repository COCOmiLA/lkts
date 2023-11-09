<?php

namespace backend\modules\i18n\models;

use Yii;












class I18nMessage extends \yii\db\ActiveRecord
{
    public $category;
    public $sourceMessage;

    


    public static function tableName()
    {
        return '{{%i18n_message}}';
    }

    


    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            [['id'], 'exist', 'targetClass'=>I18nSourceMessage::class, 'targetAttribute'=>'id'],
            [['translation'], 'string'],
            [['language'], 'string', 'max' => 16],
            [['language'], 'unique', 'targetAttribute' => ['id', 'language']]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'language' => Yii::t('backend', 'Язык'),
            'translation' => Yii::t('backend', 'Перевод'),
            'sourceMessage' => Yii::t('backend', 'Исходное сообщение'),
            'category' => Yii::t('backend', 'Категория'),
        ];
    }

    public function afterFind()
    {
        $this->sourceMessage = $this->sourceMessageModel ? $this->sourceMessageModel->message : null;
        $this->category = $this->sourceMessageModel ? $this->sourceMessageModel->category : null;
        return parent::afterFind();
    }


    


    public function getSourceMessageModel()
    {
        return $this->hasOne(I18nSourceMessage::class, ['id' => 'id']);
    }
}
