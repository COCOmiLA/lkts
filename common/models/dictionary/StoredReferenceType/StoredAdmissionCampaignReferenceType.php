<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\AttachmentType;
use common\models\dictionary\AdmissionProcedure;
use common\models\dictionary\AvailableDocumentTypesForConcession;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\DictionaryPredmetOfExamsSchedule;
use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\IndividualAchievementDocumentType;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;

class StoredAdmissionCampaignReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%admission_campaign_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Документ.ПриемнаяКампания';
    }

    public function getCompetitiveGroups()
    {
        return $this->hasMany(StoredCompetitiveGroupReferenceType::class, [
            'reference_uid' => 'competitive_group_uid'
        ])
            ->viaTable('{{%admission_campaign_competitive_group_connections}}', ['admission_campaign_uid' => 'reference_uid'])
            ->active();
    }

    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            IndividualAchievementType::class,
            'campaign_ref_id',
            'campaign_code'
        ))
            ->fill();

        (new BaseFillHandler(
            $this,
            AdmissionCampaign::class,
            'ref_id',
            'code'
        ))
            ->fill();

        (new BaseFillHandler(
            $this,
            OlympiadFilter::class,
            'campaign_ref_id',
            'id_pk'
        ))
            ->setArchiveQuery([])
            ->fill();

        (new BaseFillHandler(
            $this,
            Speciality::class,
            'campaign_ref_id',
            'campaign_code'
        ))
            ->fill();

        (new BaseFillHandler(
            $this,
            AttachmentType::class,
            'admission_campaign_ref_id',
            'campaign_code',
            ['is_using' => true]
        ))
            ->fill();

        (new BaseFillHandler(
            $this,
            IndividualAchievementDocumentType::class,
            'admission_campaign_ref_id',
            'campaign_code'
        ))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            AdmissionProcedure::class,
            'admission_campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            DictionaryCompetitiveGroupEntranceTest::class,
            'campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            IndividualAchievementType::class,
            'campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            AdmissionCampaign::class,
            'ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            OlympiadFilter::class,
            'campaign_ref_id'
        ))
            ->setArchiveQuery(null)
            ->restore();

        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            AttachmentType::class,
            'admission_campaign_ref_id',
            ['is_using' => true]
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            IndividualAchievementDocumentType::class,
            'admission_campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            AvailableDocumentTypesForConcession::class,
            'admission_campaign_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            DictionaryPredmetOfExamsSchedule::class,
            'campaign_ref_id'
        ))
            ->restore();
    }

    public static function isArchivable(): bool
    {
        
        return false;
    }
}
