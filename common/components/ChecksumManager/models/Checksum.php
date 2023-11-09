<?php

namespace common\components\ChecksumManager\models;

use Yii;
use yii\behaviors\TimestampBehavior;













class Checksum extends \yii\db\ActiveRecord
{
    public const PARAM_VENDOR = 'vendor_check_sum';
    public const LOCK_TIME = 10800; 
    public const CALC_STATUS_PROCESSING = 1;
    public const CALC_STATUS_COMPLETED = 2;
    

    


    public static function tableName()
    {
        return '{{%checksum}}';
    }

    


    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'status'], 'integer'],
            [['param', 'checksum'], 'string', 'max' => 255],
            [['path'], 'string', 'max' => 5000],
        ];
    }
    
    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('common', 'ID'),
            'param' => \Yii::t('common', 'Параметр'),
            'path' => \Yii::t('common', 'Путь'),
            'checksum' => \Yii::t('common', 'Хеш-сумма'),
            'status' => \Yii::t('common', 'Статус'),
            'created_at' => \Yii::t('common', 'Создано'),
            'updated_at' => \Yii::t('common', 'Обновлено'),
        ];
    }
    
    public static function getCurrentVendorChecksum(): ?Checksum
    {
        return Checksum::find()
            ->andWhere(['param' => Checksum::PARAM_VENDOR])
            ->orderBy(['updated_at' => 'DESC'])
            ->limit(1)
            ->one();
    }
    
    public function getStatusDescription()
    {
        switch ($this->status) {
            case static::CALC_STATUS_PROCESSING:
                return \Yii::t('common', 'В обработке');
            case static::CALC_STATUS_COMPLETED:
                return \Yii::t('common', 'Завершено');
            default:
                return '';
        }
    }
}
