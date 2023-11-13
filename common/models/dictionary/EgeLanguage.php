<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;






class EgeLanguage extends \yii\db\ActiveRecord implements IArchiveQueryable
{
    


    public static function tableName()
    {
        return '{{%foreign_languages_for_ege}}';
    }

    


    public function rules()
    {
        return [
            [['discipline_id', 'code', 'name'], 'required'],
            [['code', 'name',], 'string', 'max' => 1000],
            ['archive', 'boolean'],
            [['discipline_id'], 'integer'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'priority' => 'приоритет',
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
