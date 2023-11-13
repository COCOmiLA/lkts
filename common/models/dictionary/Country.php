<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\PersonalData;









class Country extends ModelFrom1CByOData implements IRestorableReferenceDictionary, IArchiveQueryable, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.СтраныМира';

    protected static $referenceNameColumn = 'name';


    


    public static function tableName()
    {
        return '{{%dictionary_country}}';
    }

    


    public function rules()
    {
        return [
            [['code', 'name', 'ref_key'], 'required'],
            [['code'], 'string', 'max' => 100],
            [['name',], 'string', 'max' => 255],
            [['ref_key'], 'string', 'max' => 255],
            [['full_name'], 'string', 'max' => 1000],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'code' => 'Код Страны',
            'name' => 'Наименование',
        ];
    }

    public function getDatacode()
    {
        return [
            'data-code' => $this->ref_key,
        ];
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
            'country_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler($this,
            AddressData::class,
            'country_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }

    public function fillDictionary()
    {
    }

    public static function getReferenceClassToFill(): string
    {
        return static::getReferenceClassName();
    }
}
