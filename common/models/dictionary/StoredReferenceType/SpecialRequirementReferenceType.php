<?php


namespace common\models\dictionary\StoredReferenceType;


use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\bachelor\EgeResult;

class SpecialRequirementReferenceType extends ModelFrom1CByOData implements IRestorableReferenceDictionary, IArchiveQueryable
{
    protected static $referenceClassName = 'Справочник.СпециальныеУсловия';

    protected static $referenceIdColumn = 'reference_id';

    protected static $referenceNameColumn = 'reference_name';

    protected static $referenceUidColumn = 'reference_uid';

    protected static $referenceDataVersion = 'reference_data_version';

    protected static $referenceParentUid = 'reference_parent_uid';

    public static function tableName()
    {
        return '{{%special_requirements}}';
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            EgeResult::class,
            'special_requirement_ref_id'
        ))
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