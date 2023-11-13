<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\PersonalData;
use yii\behaviors\TimestampBehavior;





class ForeignLanguage extends ModelFrom1CByOData implements IRestorableReferenceDictionary, IArchiveQueryable
{

    protected static $referenceClassName = 'Справочник.ИностранныеЯзыки';

    


    public static function tableName()
    {
        return '{{%dictionary_foreign_languages}}';
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
            [['ref_key', 'code', 'description',], 'required'],
            [['ref_key', 'parent_key'], 'string', 'max' => 255],
            [['code', 'description',], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'parent_key' => 'родительский ключ 1С',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(ForeignLanguage::class, ['ref_key' => 'parent_key']);
    }

    public function getChildren()
    {
        return $this->hasMany(ForeignLanguage::class, ['parent_key' => 'ref_key']);
    }

    public static function find()
    {
        return new DictionaryQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            PersonalData::class,
            'language_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }
}
