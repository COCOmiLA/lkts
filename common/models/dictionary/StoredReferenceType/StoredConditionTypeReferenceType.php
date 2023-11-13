<?php

namespace common\models\dictionary\StoredReferenceType;

use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CgetConditionType;

class StoredConditionTypeReferenceType extends StoredReferenceType implements
    IFillableReferenceDictionary,
    IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%condition_type_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'ПланВидовХарактеристик.ВидыУсловийДопускаКВступительнымИспытаниям';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            CgetConditionType::class,
            'condition_type_reference_type_id'
        ))
            ->restore();
    }
}
