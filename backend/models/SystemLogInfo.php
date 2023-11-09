<?php







namespace backend\models;

use Yii;












class SystemLogInfo extends \yii\db\ActiveRecord
{
    const CATEGORY_NOTIFICATION = 'notification';
    


    public static function tableName()
    {
        return '{{%system_log_info}}';
    }

    


    public function rules()
    {
        return [
            [['level', 'log_time', 'message'], 'integer'],
            [['log_time'], 'required'],
            [['prefix'], 'string'],
            [['category'], 'string', 'max' => 255]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'level' => Yii::t('backend', 'Уровень'),
            'category' => Yii::t('backend', 'Категория'),
            'log_time' => Yii::t('backend', 'Время события'),
            'prefix' => Yii::t('backend', 'Префикс'),
            'message' => Yii::t('backend', 'Сообщение'),
        ];
    }
}
