<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\PersonalData;
use yii\behaviors\TimestampBehavior;













class Gender extends ModelFrom1CByOData implements IRestorableReferenceDictionary, IArchiveQueryable
{
    protected static $referenceClassName = 'Справочник.ПолФизическихЛиц';

    


    public static function tableName()
    {
        return '{{%dictionary_gender}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [['ref_key', 'code', 'description'], 'required'],
            [['updated_at', 'created_at'], 'integer'],
            [['ref_key'], 'string', 'max' => 255],
            [['data_version', 'code'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 1000],
            [['archive'], 'boolean'],
        ];
    }


    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref_key' => 'Ref Key',
            'data_version' => 'Data Version',
            'code' => 'Code',
            'description' => 'Description',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'archive' => 'Archive',
        ];
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            PersonalData::class,
            'gender_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
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
}
