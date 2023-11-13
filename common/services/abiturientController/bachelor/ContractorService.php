<?php

namespace common\services\abiturientController\bachelor;

use common\components\AddressHelper\AddressHelper;
use common\components\LikeQueryManager;
use common\models\dictionary\Contractor;
use common\models\dictionary\Fias;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\services\abiturientController\BaseService;
use yii\helpers\ArrayHelper;

class ContractorService extends BaseService
{
    public function getHasPendingContractor(array $models, string $path): bool
    {
        foreach ($models as $model) {
            if (ArrayHelper::getValue($model, $path) === Contractor::STATUS_PENDING) {
                return true;
            }
        }

        return false;
    }

    public function checkAllPendingContractors(BachelorApplication $application): array
    {
        $has_pending_contractors_parent = false;
        foreach ($application->abiturientQuestionary->parentData as $parent_data) {
            if ($this->getHasPendingContractor([$parent_data->passportData], 'contractor.status')) {
                $has_pending_contractors_parent = true;
                break;
            }
        }

        return [
            'passport_data' => $this->getHasPendingContractor($application->abiturientQuestionary->passportData, 'contractor.status'),
            'education' => $this->getHasPendingContractor($application->educations, 'contractor.status'),
            'special_rights' => $this->getHasPendingContractor($application->bachelorPreferencesSpecialRight, 'contractor.status'),
            'targets' => $this->getHasPendingContractor($application->targetReceptions, 'targetContractor.status') || $this->getHasPendingContractor($application->targetReceptions, 'documentContractor.status'),
            'olympiads' => $this->getHasPendingContractor($application->bachelorPreferencesOlymp, 'contractor.status'),
            'ia' => $this->getHasPendingContractor($application->individualAchievements, 'contractor.status'),
            'parent_passport_data' => $has_pending_contractors_parent
        ];
    }

    public function hasAtLeastOnePendingContractor(array $blocks): bool
    {
        return !empty(array_filter($blocks, function ($el) {
            return $el;
        }));
    }

    public function searchContractor(): array
    {
        $q = $this->request->post('q');
        $page = $this->request->post('page');
        $contractor_type_ref_uid = $this->request->post('contractor_type');
        $pageSize = 30;

        $contractors = Contractor::find()->andWhere([
            Contractor::tableName() . '.archive' => false,
            Contractor::tableName() . '.status' => Contractor::STATUS_APPROVED
        ]);

        if ($q) {
            $contractors = $contractors->andWhere(
                LikeQueryManager::getFullTextSearch('name', $q)
            );
        }

        if ($contractor_type_ref_uid) {
            $contractors->joinWith('contractorTypeRef contractor_type_ref', false);
            $contractors->andWhere(['contractor_type_ref.reference_uid' => [$contractor_type_ref_uid, null]]);
        }

        $contractors = $contractors
            ->limit($pageSize)
            ->offset($pageSize * ($page - 1)) 
            ->all();

        $results = [];
        foreach ($contractors as $key => $contractor) {
            $results[$key]['id'] = $contractor['id'];
            $results[$key]['text'] = $contractor->fullname;
        }

        return [
            'results' => $results,
            'selected' => '',
            'pagination' => ['more' => !empty($contractors)]
        ];
    }

    public function searchLocation(): array
    {
        $q = $this->request->post('q');
        $page = $this->request->post('page', 1);
        $pageSize = 30;

        $citiesQuery = Fias::find()
            ->andWhere([
                'OR',
                ['address_element_type' => [AddressHelper::CITY_TYPE, AddressHelper::TOWN_TYPE]],
                ['code' => AddressHelper::federalSignificanceCityCodes()]
            ])
            ->select(['code', 'name', 'short', 'region_code', 'area_code'])
            ->asArray();

        if ($q) {
            $citiesQuery->andWhere(
                LikeQueryManager::getFullTextSearch('name', $q)
            );
        }

        $cities = $citiesQuery
            ->limit($pageSize)
            ->offset($pageSize * ($page - 1)) 
            ->asArray()
            ->all();

        $results = [];
        foreach ($cities as $key => $city) {
            $parts = [];

            $region = Fias::find()
                ->select(['name', 'short'])
                ->andWhere([
                    'address_element_type' => AddressHelper::REGION_TYPE,
                    'region_code' => $city['region_code'],
                ])
                ->one();

            if ($region && !in_array($city['code'], AddressHelper::federalSignificanceCityCodes())) {
                $parts[] = "{$region['name']} {$region['short']}";
            }

            $area = Fias::find()
                ->select(['name', 'short'])
                ->andWhere([
                    'address_element_type' => AddressHelper::AREA_TYPE,
                    'region_code' => $city['region_code'],
                    'area_code' => $city['area_code'],
                ])
                ->one();

            if ($area && !in_array($city['code'], AddressHelper::federalSignificanceCityCodes())) {
                $parts[] = "{$area['name']} {$area['short']}";
            }

            $parts[] = "{$city['name']} {$city['short']}";

            $results[$key]['id'] = $city['code'];
            $results[$key]['text'] = implode(', ', $parts);
        }

        return [
            'results' => $results,
            'selected' => '',
            'pagination' => ['more' => !empty($cities)]
        ];
    }
}
