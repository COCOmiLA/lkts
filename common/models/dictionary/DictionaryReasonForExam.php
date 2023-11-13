<?php


namespace common\models\dictionary;


use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;









class DictionaryReasonForExam extends \yii\db\ActiveRecord implements IArchiveQueryable
{
    


    public static function tableName()
    {
        return '{{%dictionary_reasons_for_exam}}';
    }

    


    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['archive'], 'boolean'],
            [['archive'], 'default', 'value' => false],
            [['code', 'name'], 'string', 'max' => 255],
            [['code'], 'unique'],
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