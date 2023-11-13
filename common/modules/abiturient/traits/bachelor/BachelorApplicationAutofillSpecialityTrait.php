<?php

namespace common\modules\abiturient\traits\bachelor;

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\models\dictionary\Speciality;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\repositories\SpecialityRepository;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

trait BachelorApplicationAutofillSpecialityTrait
{
    





    public function getSpecialitiesForAutofillQuery(): ActiveQuery
    {
        

        $budgetBasisGuid = Yii::$app->configurationManager->getCode('budget_basis_guid'); 
        $targetReceptionGuid = Yii::$app->configurationManager->getCode('target_reception_guid'); 

        $tnCampaigns = '{{%campaigns}}';

        $tnSpeciality = Speciality::tableName();
        $tnAdmissionCampaign = AdmissionCampaign::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnEducationSource = '{{%education_source_ref}}';

        $queryCampaignRefId = $this->getType()
            ->select("{$tnAdmissionCampaign}.ref_id")
            ->joinWith('campaign');

        $querySpecialitiesIsNotOnlyBudgetCategory = Speciality::find()
            ->select("{$tnSpeciality}.curriculum_ref_id")
            ->active()
            ->joinWith('educationSourceRef education_source_ref', false)
            ->andWhere(['IN', "{$tnSpeciality}.campaign_ref_id", $queryCampaignRefId])
            ->andWhere([
                'IN',
                "{$tnSpeciality}.id",
                $this
                    ->getSpecialitiesWithoutOrdering()
                    ->select("{$tnBachelorSpeciality}.speciality_id")
            ])
            ->andWhere([
                'OR',
                ['IN', "{$tnEducationSource}.reference_uid", [$targetReceptionGuid]],
                ["{$tnSpeciality}.special_right" => true]
            ]);

        $excludeSpecialtyIds = [];
        $session = Yii::$app->session;
        if ($session->has('notSelectedSpecialtyIds')) {
            $excludeSpecialtyIds = $session->get('notSelectedSpecialtyIds');
        }

        return Speciality::find()
            ->active()
            ->joinWith('educationSourceRef education_source_ref', false)
            ->joinWith('educationFormRef education_form_ref', false)
            ->joinWith('detailGroupRef detail_group_ref', false)
            ->joinWith('campaignRef campaign_ref', false)
            ->leftJoin(
                [$tnCampaigns => SpecialityRepository::getAdmissionCampaignQuery()],
                SpecialityRepository::getJoinQueryForAdmissionCampaign(),
            )
            ->andWhere(["{$tnSpeciality}.special_right" => false])
            ->andWhere(['NOT IN', "{$tnSpeciality}.id", $excludeSpecialtyIds])
            ->andWhere(['IN', "{$tnSpeciality}.campaign_ref_id", $queryCampaignRefId])
            ->andWhere(['IN', "{$tnEducationSource}.reference_uid", [$budgetBasisGuid]])
            ->andWhere(['IN', "{$tnSpeciality}.curriculum_ref_id", $querySpecialitiesIsNotOnlyBudgetCategory])
            ->andWhere(['>=', IndependentQueryManager::strToDateTime("{$tnCampaigns}.date_final"), date('Y-m-d H:i:s')])
            ->andWhere(['<=', IndependentQueryManager::strToDateTime("{$tnCampaigns}.date_start"), date('Y-m-d H:i:s')]);
    }

    





    public function hasSpecialitiesForAutofill(): bool
    {
        

        if ($this->status != BachelorApplication::STATUS_CREATED) {
            return false;
        }
        $tnSpeciality = Speciality::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();

        $baseQuery = $this->getSpecialitiesForAutofillQuery()
            ->groupBy("{$tnSpeciality}.id");

        $requiredQuantity = (new Query())
            ->select(['COUNT(*)'])
            ->from(['required_table' => $baseQuery]);

        $availableQuantity = (new Query())
            ->select(['COUNT(*)'])
            ->from(['available_table' => $this
                ->getSpecialitiesWithoutOrdering()
                ->andWhere([
                    'IN',
                    "{$tnBachelorSpeciality}.speciality_id",
                    $baseQuery->select("{$tnSpeciality}.id")
                ])]);

        $havingQuery = (Yii::$app->db->driverName === 'pgsql') ?
            '"required_quantity" != "available_quantity"' :
            '`required_quantity` != `available_quantity`';
        return (new Query())
            ->from(['comparing_table' => (new Query())->select([
                'required_quantity' => $requiredQuantity,
                'available_quantity' => $availableQuantity,
            ])])
            ->groupBy(['required_quantity', 'available_quantity'])
            ->having($havingQuery)
            ->exists();
    }

    




    public function getSpecialitiesForAutofill(): array
    {
        

        $tnSpeciality = Speciality::tableName();

        return $this
            ->getSpecialitiesForAutofillQuery()
            ->select("{$tnSpeciality}.id")
            ->groupBy("{$tnSpeciality}.id")
            ->column();
    }
}
