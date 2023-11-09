<?php

namespace common\services\abiturientController\sandbox;

use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationSearch;
use common\services\abiturientController\BaseService;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class SandboxApplicationsTableService extends BaseService
{
    





    public function buildAppicationsDataForSandbox(User $currentUser, string $sandboxTableType): array
    {
        $searchModel = new ApplicationSearch();

        $listOfAdmissionCampaign = $this->getListOfAdmissionCampaignNonArchive($currentUser);

        $applicationsDataProvider = $searchModel->search(
            $this->request->get(),
            $sandboxTableType,
            ArrayHelper::getColumn($listOfAdmissionCampaign, 'reference_uid')
        );
        return [
            'applications' => $applicationsDataProvider,
            'searchModel' => $searchModel,
            'currentUser' => $currentUser,
            'type' => $sandboxTableType,
            'listOfAdmissionCampaign' => $listOfAdmissionCampaign
        ];
    }

    




    private function getListOfAdmissionCampaignQuery(User $currentUser): Query
    {
        if ($currentUser->isViewer()) {
            return (new Query())
                ->select('admission_campaign_reference_type.reference_uid, application_type.name')
                ->from('application_type')
                ->leftJoin('{{%viewer_admission_campaign_junctions}}', 'application_type.id = viewer_admission_campaign_junctions.application_type_id')
                ->leftJoin('admission_campaign', 'admission_campaign.id = application_type.campaign_id')
                ->leftJoin('admission_campaign_reference_type', 'admission_campaign_reference_type.id = admission_campaign.ref_id')
                ->where(['user_id' => $currentUser->id]);
        }
        return (new Query())
            ->select('admission_campaign_reference_type.reference_uid, application_type.name')
            ->from('application_type')
            ->leftJoin('{{%moderate_admission_campaign}}', 'application_type.id = moderate_admission_campaign.application_type_id')
            ->leftJoin('admission_campaign', 'admission_campaign.id = application_type.campaign_id')
            ->leftJoin('admission_campaign_reference_type', 'admission_campaign_reference_type.id = admission_campaign.ref_id')
            ->where(['rbac_auth_assignment_user_id' => $currentUser->id]);
    }

    




    private function getListOfAdmissionCampaignNonArchive(User $currentUser): array
    {
        return $this->getListOfAdmissionCampaignQuery($currentUser)
            ->andWhere(['admission_campaign.archive' => false])
            ->all();
    }
}
