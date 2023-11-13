<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IReferencesOData;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\bachelor\CgetConditionType;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;
use common\modules\abiturient\models\bachelor\EducationData;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;


class EducationType extends ModelFrom1CByOData implements IReferencesOData, IRestorableReferenceDictionary, IArchiveQueryable
{
    protected static $referenceClassName = 'Справочник.ВидыОбразований';

    public static function tableName()
    {
        return '{{%dictionary_education_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return static::$referenceClassName;
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
            [['ref_key', 'code', 'description'], 'required'],
            [['ref_key', 'parent_key'], 'string', 'max' => 255],
            [['code', 'description'], 'string', 'max' => 1000],
            [['archive'], 'boolean'],
            [['data_version'], 'string', 'max' => 100],
            [['data_version'], 'default', 'value' => ''],
            [['ref_key'], 'unique', 'targetAttribute' => ['ref_key', 'data_version', 'archive']]
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
        return $this->hasOne(DocumentType::class, ['ref_key' => 'parent_key']);
    }

    public function getChildren()
    {
        return $this->hasMany(DocumentType::class, ['parent_key' => 'ref_key']);
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

    public static function updateLinks()
    {
        $all_items = EducationType::find()
            ->active()
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {
                Speciality::updateAll(['education_program_ref_id' => ArrayHelper::getValue($item, 'id')], [
                    'dictionary_speciality.eduprogram_code' => $item->{EducationType::$codeColumnName},
                    'dictionary_speciality.education_program_ref_id' => null
                ]);
            }
        }
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'education_program_ref_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CgetEntranceTestSet::class,
            'education_type_ref_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            EducationDataFilter::class,
            'education_type_id'
        ))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            EducationData::class,
            'education_type_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CgetConditionType::class,
            'dictionary_education_type_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }
}
