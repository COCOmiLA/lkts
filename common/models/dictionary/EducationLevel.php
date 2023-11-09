<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;
use yii\behaviors\TimestampBehavior;






class EducationLevel extends \yii\db\ActiveRecord implements IArchiveQueryable
{
    


    public static function tableName()
    {
        return '{{%dictionary_education_level}}';
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
            [['ref_key', 'data_version', 'code', 'description', 'user_category'], 'required'],
            [['ref_key', 'type_key'], 'string', 'max' => 255],
            [['code', 'description', 'user_category'], 'string', 'max' => 1000],
            [['short_name','level_code'], 'string', 'max' => 100],
            ['level_code', 'default', 'value' => ''],
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'user_category' => 'категория',
            'type_key' => 'ключ типа 1С',
            'short_name' => 'сокращенное наименование',
            'level_code' => 'код уровня подготовки',
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
