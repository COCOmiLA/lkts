<?php


namespace common\modules\abiturient\models\repositories;


use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\IndividualAchievementDocumentType;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class IndividualAchievementDocumentTypesRepository
{
    





    public static function GetDocumentTypesByIndividualAchievementTypeAndCampaignQuery(
        StoredAdmissionCampaignReferenceType $campaignRef,
        IndividualAchievementType            $individualAchievementType = null,
        IndividualAchievementDocumentType    $chosenDocumentType = null
    ): ActiveQuery
    {
        $mainQuery = [
            'admission_campaign_ref.reference_uid' => $campaignRef->reference_uid,
            'individual_achievements_document_types.archive' => false
        ];

        $iaDocumentTypesQuery = IndividualAchievementDocumentType::find()
            ->joinWith('availableDocumentTypeFilterRef availableDocumentTypeFilterRef')
            ->joinWith(['admissionCampaignRef admission_campaign_ref'])
            ->andWhere($mainQuery)
            ->orderBy('custom_order');

        if (!is_null($individualAchievementType)) {
            $achievementGUID = ArrayHelper::getValue($individualAchievementType, 'achievementCategoryRef.reference_uid');
            if (!is_null($achievementGUID)) {
                $iaDocumentTypesQuery
                    ->andWhere([
                        'availableDocumentTypeFilterRef.reference_uid' => $achievementGUID
                    ]);
            }
        }

        if (!is_null($chosenDocumentType)) {
            
            $iaDocumentTypesQuery->orWhere([
                'individual_achievements_document_types.id' => $chosenDocumentType->id
            ]);
        }

        $iaDocumentTypesQuery->orWhere(
            ArrayHelper::merge(
                $mainQuery,
                ['availableDocumentTypeFilterRef.id' => null]
            )
        );
        return IndividualAchievementDocumentType::find()
            ->where([
                IndividualAchievementDocumentType::tableName() . '.id' => $iaDocumentTypesQuery
                    ->select([IndividualAchievementDocumentType::tableName() . '.id'])
            ]);
    }

    public static function GetDocumentTypesByIndividualAchievementTypeAndCampaign(
        StoredAdmissionCampaignReferenceType $campaignRef,
        IndividualAchievementType            $individualAchievementType = null,
        IndividualAchievementDocumentType    $chosenDocumentType = null
    ): array
    {
        return \Yii::$app->cache->getOrSet(
            ['GetDocumentTypesByIndividualAchievementTypeAndCampaign', $campaignRef->reference_uid, $individualAchievementType->ach_category_ref_id ?? null, $chosenDocumentType->document_type_ref_id ?? null],
            function () use ($chosenDocumentType, $individualAchievementType, $campaignRef) {
                return self::GetDocumentTypesByIndividualAchievementTypeAndCampaignQuery(
                    $campaignRef,
                    $individualAchievementType,
                    $chosenDocumentType
                )
                    ->all();
            },
            3600
        );
    }
}
