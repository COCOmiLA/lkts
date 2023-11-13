<?php

namespace common\models\dictionary\StoredReferenceType;

use common\components\BooleanCaster;
use common\components\queries\DictionaryQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\EmptyCheck;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\ICanBuildReferenceTypeArrayTo1C;
use common\models\interfaces\IHaveReferenceClassName;
use common\models\interfaces\IReferenceCanUpdate;
use common\models\traits\ColumnExistsTrait;
use common\modules\student\models\ReferenceType;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


















class StoredReferenceType extends ActiveRecord implements
    ICanBuildReferenceTypeArrayTo1C,
    IHaveReferenceClassName,
    IReferenceCanUpdate,
    IArchiveQueryable
{
    use ColumnExistsTrait;

    protected static $required_fields = [
        'reference_name',
        'reference_class_name',
        'reference_id',
        'reference_uid',
        'archive',
    ];

    


    public static function tableName()
    {
        return '{{%reference_type}}';
    }


    public function behaviors()
    {
        return [['class' => TimestampBehavior::class]];
    }

    


    public function rules()
    {
        return [
            [
                static::$required_fields,
                'required'
            ],
            [
                [
                    'reference_name',
                    'reference_class_name',
                    'predefined_data_name',
                ],
                'string',
                'max' => 1000
            ],
            [
                [
                    'reference_id',
                    'reference_data_version',
                    'reference_uid',
                    'reference_parent_uid'
                ],
                'string',
                'max' => 255
            ],
            [
                [
                    'archive',
                    'is_folder',
                    'has_deletion_mark',
                    'posted',
                    'is_predefined',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'is_folder',
                    'has_deletion_mark',
                    'posted',
                    'is_predefined',
                ],
                'default',
                'value' => false
            ],
        ];
    }

    public static function isArchivable(): bool
    {
        return true;
    }

    public function buildReferenceTypeArrayTo1C(): array
    {
        return [
            'ReferenceName' => $this->reference_name ?? '',
            'ReferenceId' => $this->reference_id ?? '',
            'ReferenceUID' => $this->reference_uid ?? '00000000-0000-0000-0000-000000000000',
            'ReferenceClassName' => $this->reference_class_name ?? '',
            'ReferenceParentUID' => $this->reference_parent_uid ?? '00000000-0000-0000-0000-000000000000',
            'ReferenceDataVersion' => $this->reference_data_version ?? '',
            'IsFolder' => (int)BooleanCaster::cast($this->is_folder),
            'DeletionMark' => (int)BooleanCaster::cast($this->has_deletion_mark),
            'Posted' => (int)BooleanCaster::cast($this->posted),
            'Predefined' => (int)BooleanCaster::cast($this->is_predefined),
            'PredefinedDataName' => $this->predefined_data_name ?? '',
        ];
    }

    public function archive(): bool
    {
        $this->archive = true;
        return $this->save();
    }

    



    public static function findByReferenceType($referenceMappedData)
    {
        if (!($referenceMappedData instanceof ReferenceType)) {
            $referenceMappedData = ReferenceType::BuildRefFromXML($referenceMappedData);
        }
        if (is_null($referenceMappedData)) {
            return null;
        }
        $query = static::find()->where([
            'reference_class_name' => $referenceMappedData->referenceClassName,
            'archive' => false
        ]);
        if (!ReferenceTypeManager::isEnumerateReferenceType($referenceMappedData->referenceClassName)) {
            
            $query->andWhere([
                'reference_uid' => $referenceMappedData->referenceUID,
            ])->andFilterWhere([
                'reference_data_version' => EmptyCheck::presence($referenceMappedData->referenceDataVersion),
            ]);
        } else {
            $query->andWhere([
                'reference_name' => $referenceMappedData->referenceName,
            ]);
        }
        return $query->limit(1)->one();
    }

    public static function getArchiveStateOrderingCondition()
    {
        return ['archive' => SORT_ASC];
    }

    public static function getArchiveCondition()
    {
        return [static::tableName() . '.' . static::getArchiveColumnName() => static::getArchiveColumnNegativeValue()];
    }

    public static function getArchiveColumnName()
    {
        return 'archive';
    }

    public static function getArchiveColumnNegativeValue()
    {
        return false;
    }

    public static function getArchiveColumnPositiveValue()
    {
        return true;
    }

    public static function getNameColumnName()
    {
        return 'reference_name';
    }

    public static function getIdColumnName()
    {
        return 'reference_id';
    }

    public static function getUidColumnName()
    {
        return 'reference_uid';
    }

    public static function getDataVersionColumnName()
    {
        return 'reference_data_version';
    }

    public static function getDeletionMarkColumnName()
    {
        return 'has_deletion_mark';
    }

    public static function getQuerySetByUID(string $uid, bool $all = false)
    {
        $query = static::find()->where([
            'reference_uid' => $uid,
        ]);
        if (!$all) {
            $query->andWhere(['archive' => false]);
        } else {
            $query->orderBy(static::getArchiveStateOrderingCondition());
        }
        $query->addOrderBy([static::getDataVersionColumnName() => SORT_ASC]);

        return $query;
    }

    public static function findByUID(string $uid, bool $all = false)
    {
        $query = static::getQuerySetByUID($uid, $all);

        return $query->limit(1)->one();
    }

    public static function findByCode(string $code)
    {
        return static::findOne([
            'reference_id' => $code,
        ]);
    }

    public static function getQuerySetByName(string $name, bool $all = false)
    {
        $query = static::find()->where([
            'reference_name' => $name,
        ]);
        if (!$all) {
            $query->andWhere(['archive' => false]);
        } else {
            $query->orderBy(static::getArchiveStateOrderingCondition());
        }
        $query->addOrderBy([static::getDataVersionColumnName() => SORT_ASC]);

        return $query;
    }

    public static function findByName(string $name, bool $all = false)
    {
        $query = static::getQuerySetByName($name, $all);

        return $query->limit(1)->one();
    }

    public function loadDataFromMappedReference(ReferenceType $referenceType)
    {
        $this->reference_name = $referenceType->referenceName;
        $this->reference_class_name = $referenceType->referenceClassName;
        $this->reference_id = $referenceType->referenceId;
        $this->reference_uid = $referenceType->referenceUID;
        $this->reference_data_version = $referenceType->referenceDataVersion;
        $this->reference_parent_uid = $referenceType->referenceParentUID;
        $this->is_folder = $referenceType->is_folder;
        $this->has_deletion_mark = $referenceType->has_deletion_mark;
        $this->posted = $referenceType->posted;
        $this->is_predefined = $referenceType->is_predefined;
        $this->predefined_data_name = $referenceType->predefined_data_name;
        $this->archive = false;
    }

    


    public static function getReferenceClassName(): string
    {
        
        return static::getReferenceClassToFill();
    }

    public static function getReferenceClassToFill(): string
    {
        return '';
    }

    public function setUpdateScenario()
    {
        return null;
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

    public function __toString()
    {
        return json_encode($this->buildReferenceTypeArrayTo1C());
    }
}
