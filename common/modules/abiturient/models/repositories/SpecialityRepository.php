<?php


namespace common\modules\abiturient\models\repositories;

use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\queries\ArchiveQuery;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class SpecialityRepository
{
    


    public static function getAdmissionCampaignQuery(): ActiveQuery
    {
        $categorySpecificLawUid = Yii::$app->configurationManager->getCode('category_specific_law');

        return AdmissionCampaign::find()
            ->select([
                'date_start',
                'date_final',
                'campaign_info_education_source_ref.reference_uid info_education_source_ref_uid',
                'campaign_info_education_form_ref.reference_uid info_education_form_ref_uid',
                'campaign_info_detail_group_ref.reference_uid info_detail_group_ref_uid',
                'campaign_info_admission_category.ref_key info_admission_category',
                'reference_type.reference_uid info_campaign_ref_uid',
                new Expression("(CASE WHEN campaign_info_admission_category.ref_key = :category_specific_law_uid THEN TRUE ELSE FALSE END) info_is_special_right", [':category_specific_law_uid' => $categorySpecificLawUid]),
                AdmissionCampaign::tableName() . '.archive campaign_archive',
                CampaignInfo::tableName() . '.archive info_archive',
            ])
            ->joinWith('referenceType reference_type', false)
            ->joinWith(['info' => function ($q) {
                $q->joinWith('educationSourceRef campaign_info_education_source_ref', false);
                $q->joinWith('educationFormRef campaign_info_education_form_ref', false);
                $q->joinWith('detailGroupRef campaign_info_detail_group_ref', false);
                $q->joinWith('admissionCategory campaign_info_admission_category', false);
            }], false);
    }

    


    public static function getJoinQueryForAdmissionCampaign(): string
    {
        return 'info_campaign_ref_uid = campaign_ref.reference_uid AND '
            . 'info_education_source_ref_uid = education_source_ref.reference_uid AND '
            . 'info_education_form_ref_uid = education_form_ref.reference_uid AND '
            . 'info_is_special_right = dictionary_speciality.special_right AND '
            
            . '(
                info_detail_group_ref_uid = detail_group_ref.reference_uid OR
                (
                    info_detail_group_ref_uid IS NULL AND
                    detail_group_ref.reference_uid IS NULL
                )
           )';
    }

    








    public static function getCurrentAvailableSpecialities(BachelorApplication $application, bool $allowBenefitCategories = true)
    {
        $categorySpecificLawUid = Yii::$app->configurationManager->getCode('category_specific_law');
        $campaignRefUid = ArrayHelper::getValue($application, 'type.campaign.referenceType.reference_uid');

        $requestTime = date('Y-m-d H:i:s');

        $query = Speciality::find()
            ->joinWith('educationSourceRef education_source_ref', false)
            ->joinWith('educationFormRef education_form_ref', false)
            ->joinWith('detailGroupRef detail_group_ref', false)
            ->joinWith('directionRef direction_ref', false)
            ->joinWith('competitiveGroupRef competitive_group_ref', false)
            ->joinWith('campaignRef campaign_ref', false)
            ->leftJoin(
                ['campaigns' => SpecialityRepository::getAdmissionCampaignQuery()],
                SpecialityRepository::getJoinQueryForAdmissionCampaign(),
            )
            ->andWhere([Speciality::tableName() . '.receipt_allowed' => true])
            ->andWhere([Speciality::tableName() . '.archive' => false])
            ->andWhere(['campaign_ref.reference_uid' => $campaignRefUid])
            ->andWhere(['<=', IndependentQueryManager::strToDateTime('campaigns.date_start'), $requestTime])
            ->andWhere(['>=', IndependentQueryManager::strToDateTime('campaigns.date_final'), $requestTime])
            ->andWhere(['campaigns.campaign_archive' => false])
            ->andWhere(['campaigns.info_archive' => false])
            ->orderBy([
                'direction_ref.reference_name' => SORT_ASC,
                'competitive_group_ref.reference_name' => SORT_ASC,
            ]);

        if (!$allowBenefitCategories) {
            $query = $query->andWhere(['!=', 'info_admission_category', $categorySpecificLawUid]);
        }
        $selected_speciality_ids = $application->getSpecialities()->select([BachelorSpeciality::tableName() . '.speciality_id']);
        if ($application->type->rawCampaign->multiply_applications_allowed) {
            
            $target_reception_guid = Yii::$app->configurationManager->getCode('target_reception_guid');
            if ($target_reception_guid) {
                $selected_speciality_ids = $selected_speciality_ids
                    ->joinWith(['speciality.educationSourceRef'])
                    ->andWhere(['not', [StoredEducationSourceReferenceType::tableName() . '.reference_uid' => $target_reception_guid]]);
            }
        }
        return $query->andWhere(['not in', 'dictionary_speciality.id', $selected_speciality_ids]);
    }

    public static function getSpecialityFiltersData(BachelorApplication $application)
    {
        $campaign_ref_uid = $application->type->rawCampaign->referenceType->reference_uid;
        return Yii::$app->cache->getOrSet(
            "speciality_filters_data_{$campaign_ref_uid}",
            function () use ($campaign_ref_uid) {
                $campaign_specialities = Speciality::find()
                    ->active()
                    ->joinWith('campaignRef campaign_ref', false)
                    ->with('subdivisionRef')
                    ->with('competitiveGroupRef')
                    ->with('educationSourceRef')
                    ->with('educationFormRef')
                    ->with('detailGroupRef')
                    ->andWhere(['campaign_ref.reference_uid' => $campaign_ref_uid])
                    ->all();

                $department_array = ArrayHelper::map($campaign_specialities, 'subdivisionRef.reference_uid', 'subdivisionRef.reference_name');
                $eduform_array = ArrayHelper::map($campaign_specialities, 'educationFormRef.reference_uid', 'educationFormRef.reference_name');
                $detail_groups_array = ArrayHelper::map($campaign_specialities, 'detailGroupRef.reference_uid', 'detailGroupRef.reference_name');
                $finance_array = ArrayHelper::map($campaign_specialities, 'educationSourceRef.reference_uid', 'educationSourceRef.reference_name');
                $groups_array = ArrayHelper::map($campaign_specialities, 'competitiveGroupRef.reference_uid', 'competitiveGroupRef.reference_name');

                return compact(
                    'groups_array',
                    'eduform_array',
                    'finance_array',
                    'department_array',
                    'detail_groups_array',
                );
            },
            3600
        );
    }
}
