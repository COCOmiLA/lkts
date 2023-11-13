<?php


namespace common\models;


use common\components\BooleanCaster;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\interfaces\ICanBeFoundByRefType;
use common\models\interfaces\ICanBuildReferenceTypeArrayTo1C;
use common\models\interfaces\IReferenceCanUpdate;
use common\modules\student\models\ReferenceType;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;







class ModelFrom1CByOData extends ReferenceTypeModelFrom1C implements ICanBeFoundByRefType, ICanBuildReferenceTypeArrayTo1C, IReferenceCanUpdate
{
    protected static $referenceIdColumn = 'code';

    protected static $referenceNameColumn = 'description';

    protected static $referenceUidColumn = 'ref_key';

    protected static $referenceDataVersion = 'data_version';

    protected static $referenceParentUid = 'parent_key';

    protected static $is_folder = 'is_folder';

    protected static $has_deletion_mark = 'has_deletion_mark';

    protected static $posted = 'posted';

    protected static $is_predefined = 'is_predefined';

    protected static $predefined_data_name = 'predefined_data_name';

    private const ReferenceUpdateScenario = 'reference.update';


    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::ReferenceUpdateScenario => [
                static::$referenceIdColumn,
                static::$referenceUidColumn,
                static::$referenceNameColumn,
                static::$referenceDataVersion,
                static::$referenceParentUid,
                static::$is_folder,
                static::$has_deletion_mark,
                static::$posted,
                static::$is_predefined,
                static::$predefined_data_name,
            ]
        ]);
    }

    public function buildReferenceTypeArrayTo1C(): array
    {
        return [
            'ReferenceName' => $this->{static::$referenceNameColumn} ?? '',
            'ReferenceId' => $this->{static::$referenceIdColumn} ?? '',
            'ReferenceUID' => $this->{static::$referenceUidColumn} ?? '00000000-0000-0000-0000-000000000000',
            'ReferenceClassName' => static::$referenceClassName,
            'ReferenceParentUID' => $this->{static::$referenceParentUid} ?? '00000000-0000-0000-0000-000000000000',
            'ReferenceDataVersion' => $this->{static::$referenceDataVersion} ?? '',
            'IsFolder' => (int)BooleanCaster::cast($this->{static::$is_folder}),
            'DeletionMark' => (int)BooleanCaster::cast($this->{static::$has_deletion_mark}),
            'Posted' => (int)BooleanCaster::cast($this->{static::$posted}),
            'Predefined' => (int)BooleanCaster::cast($this->{static::$is_predefined}),
            'PredefinedDataName' => $this->{static::$predefined_data_name} ?? '',
        ];
    }

    



    public static function findByReferenceType($referenceData): ?ActiveRecord
    {
        if (ReferenceTypeManager::isReferenceTypeEmpty($referenceData)) {
            return null;
        }
        $reference = ReferenceType::BuildRefFromXML($referenceData);
        return static::findByCastedReferenceType($reference);
    }

    




    public static function findByCastedReferenceType(ReferenceType $reference): ?ActiveRecord
    {
        $query = static::find();
        if (!ReferenceTypeManager::isEnumerateReferenceType($reference->referenceClassName)) {
            
            $query->andWhere([
                static::$referenceUidColumn => $reference->referenceUID,
            ])->andFilterWhere([
                static::$referenceDataVersion => EmptyCheck::presence($reference->referenceDataVersion),
            ]);
        } else {
            $query->andWhere([
                static::$referenceNameColumn => $reference->referenceName,
            ]);
        }
        if (static::isArchivable()) {
            $query->andWhere([
                static::$archiveColumnName => static::$archiveColumnNegativeValue
            ]);
        }
        return $query->limit(1)->one();
    }


    public static function getQuerySetByUID(string $uid, bool $all = false)
    {
        $query = static::find()->where([
            static::$referenceUidColumn => $uid,
        ]);
        if (static::isArchivable()) {
            if (!$all) {
                $query->andWhere([
                    static::$archiveColumnName => static::$archiveColumnNegativeValue
                ]);
            } else {
                $query->orderBy(static::getArchiveStateOrderingCondition());
            }
        }
        $query->addOrderBy([static::getDataVersionColumnName() => SORT_ASC]);

        return $query;
    }

    public static function findByUID(string $uid, bool $all = false)
    {
        $query = static::getQuerySetByUID($uid, $all);

        return $query->limit(1)->one();
    }

    public static function getArchiveStateOrderingCondition()
    {
        return [static::$archiveColumnName => SORT_ASC];
    }

    public static function getQuerySetByName(string $name, bool $all = false)
    {
        $query = static::find()->where([
            static::$referenceNameColumn => $name,
        ]);
        if (static::isArchivable()) {
            if (!$all) {
                $query->andWhere([
                    static::$archiveColumnName => static::$archiveColumnNegativeValue
                ]);
            } else {
                $query->orderBy(static::getArchiveStateOrderingCondition());
            }
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
        $this->{static::$referenceNameColumn} = $referenceType->referenceName;
        $this->{static::$referenceIdColumn} = $referenceType->referenceId;
        $this->{static::$referenceUidColumn} = $referenceType->referenceUID;
        $this->{static::$referenceDataVersion} = $referenceType->referenceDataVersion;
        $this->{static::$referenceParentUid} = $referenceType->referenceParentUID;
        $this->{static::$is_folder} = $referenceType->is_folder;
        $this->{static::$has_deletion_mark} = $referenceType->has_deletion_mark;
        $this->{static::$posted} = $referenceType->posted;
        $this->{static::$is_predefined} = $referenceType->is_predefined;
        $this->{static::$predefined_data_name} = $referenceType->predefined_data_name;

        if (static::isArchivable()) {
            $this->{static::$archiveColumnName} = static::$archiveColumnNegativeValue;
        }
    }

    public static function getNameColumnName()
    {
        return static::$referenceNameColumn;
    }

    public static function getIdColumnName()
    {
        return static::$referenceIdColumn;
    }

    public static function getUidColumnName()
    {
        return static::$referenceUidColumn;
    }

    public static function getDataVersionColumnName()
    {
        return static::$referenceDataVersion;
    }

    public static function getDeletionMarkColumnName()
    {
        return 'has_deletion_mark';
    }

    public static function getArchiveColumnName()
    {
        if (!static::isArchivable()) {
            return null;
        }
        return static::$archiveColumnName;
    }

    public static function getArchiveColumnNegativeValue()
    {
        return static::$archiveColumnNegativeValue;
    }

    public static function getArchiveColumnPositiveValue()
    {
        return static::$archiveColumnPositiveValue;
    }

    public static function getArchiveCondition()
    {
        if (!static::isArchivable()) {
            return null;
        }
        return [static::tableName() . '.' . static::$archiveColumnName => static::$archiveColumnNegativeValue];
    }

    public function archive(): bool
    {
        if (!static::isArchivable()) {
            return false;
        }
        $this->{static::$archiveColumnName} = static::$archiveColumnPositiveValue;
        return $this->save();
    }

    public function setUpdateScenario()
    {
        $this->setScenario(self::ReferenceUpdateScenario);
    }

    public function __toString()
    {
        return json_encode($this->buildReferenceTypeArrayTo1C());
    }
}