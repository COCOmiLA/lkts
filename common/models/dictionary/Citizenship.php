<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;
use yii\behaviors\TimestampBehavior;








class Citizenship extends \yii\db\ActiveRecord implements IArchiveQueryable
{
    


    public static function tableName()
    {
        return '{{%dictionary_citizenship}}';
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
            [['ref_key', 'data_version', 'code', 'description'], 'required'],
            [['ref_key', ], 'string', 'max' => 255],
            [['code', 'description',], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
            ['ref_key', 'unique']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
        ];
    }
    
    public function getDatacode(){
        return [
            'data-code' => $this->code,
        ];
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }
}
