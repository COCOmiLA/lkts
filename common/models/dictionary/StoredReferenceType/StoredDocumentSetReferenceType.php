<?php

namespace common\models\dictionary\StoredReferenceType;

use common\models\AttachmentType;
use common\models\dictionary\AvailableDocumentTypesForConcession;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\IndividualAchievementDocumentType;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredDocumentSetReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%document_set_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.НаборыДокументовПредоставляемыхПоступающими';
    }

    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            AttachmentType::class,
            'document_set_ref_id',
            'document_set_code', ['is_using' => true]))
            ->fill();

        (new BaseFillHandler($this,
            IndividualAchievementDocumentType::class,
            'document_set_ref_id',
            'document_set_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            AttachmentType::class,
            'document_set_ref_id',
            ['is_using' => true]))
            ->restore();

        (new BaseRestoreHandler($this,
            IndividualAchievementDocumentType::class,
            'document_set_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            AvailableDocumentTypesForConcession::class,
            'document_set_ref_id'))
            ->restore();
    }
}
