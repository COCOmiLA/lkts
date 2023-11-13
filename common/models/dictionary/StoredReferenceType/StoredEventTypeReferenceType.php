<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;





class StoredEventTypeReferenceType extends StoredVariantOfRetestReferenceType implements
    IFillableReferenceDictionary,
    IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%event_type_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Перечисление.ТипыМероприятий';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            DictionaryDateTimeOfExamsSchedule::class,
            'event_type_ref_id'
        ))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }
}
