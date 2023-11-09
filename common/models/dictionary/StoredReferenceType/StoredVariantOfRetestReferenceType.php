<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredVariantOfRetestReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    protected static $required_fields = [
        'reference_name', 'reference_class_name', 'archive'
    ];

    public static function tableName()
    {
        return '{{%variant_of_retest_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Перечисление.ВариантыПерезачетаОлимпиады';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            OlympiadFilter::class,
            'variant_of_retest_ref_id'))
            ->setArchiveQuery(null)
            ->restore();
    }
}