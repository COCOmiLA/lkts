<?php

namespace common\services\abiturientController\questionary;

use common\components\AddressHelper\AddressHelper;
use common\components\LikeQueryManager;
use common\models\dictionary\Fias;
use common\models\EmptyCheck;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class AddressDataService extends AbiturientQuestionaryService
{
    private const PAGE_SIZE = 30;

    


    public function getDepDropParamsFromPost(): array
    {
        return [
            'params' => $this->request->post('depdrop_params'),
            'parents' => $this->request->post('depdrop_parents'),
        ];
    }

    





    public function formattingDataForSelector(array $rawData, string $textTemplate = '{{name}}'): array
    {
        $formattedData = [];
        foreach ($rawData as $key => $data) {
            $formattedData[$key]['id'] = ArrayHelper::getValue($data, 'code');

            $formattedData[$key]['text'] = strtr(
                $textTemplate,
                $this->buildReplacePairsForStrtr($textTemplate, $data),
            );
        };

        return $formattedData;
    }

    





    private function getAllRegionsQuery(
        ?string $regionCode = null,
        bool    $selectQuery = false
    ): ActiveQuery {
        $query = Fias::find()
            ->andWhere(['address_element_type' => AddressHelper::REGION_TYPE]);

        if (!EmptyCheck::isLoadingStringOrEmpty($regionCode)) {
            $query->andWhere(['code' => $regionCode]);
        }
        if ($selectQuery) {
            $query->select('region_code');
        }
        return $query->orderBy([
            'name' => SORT_ASC,
            'code' => SORT_ASC
        ]);
    }

    


    public function getAllRegions(): array
    {
        $query = $this->getAllRegionsQuery();

        $query = $this->addFilteringQuery($query);
        return ($this->setPageOffset($query))->all();
    }

    






    private function getAllAreasQuery(
        string  $regionCode,
        ?string $ariaCode = null,
        bool    $selectQuery = false
    ): ActiveQuery {
        $query = Fias::find()
            ->andFilterWhere(['IN', 'region_code', $this->getAllRegionsQuery($regionCode, true)])
            ->andFilterWhere(['address_element_type' => AddressHelper::AREA_TYPE]);

        if (!EmptyCheck::isLoadingStringOrEmpty($ariaCode)) {
            $query->andWhere(['code' => $ariaCode]);
        }
        if ($selectQuery) {
            $query->select('area_code');
        }
        return $query->orderBy([
            'name' => SORT_ASC,
            'code' => SORT_ASC
        ]);
    }

    




    public function getAllAreas(string $regionCode): array
    {
        $query = $this->getAllAreasQuery($regionCode);

        $query = $this->addFilteringQuery($query);
        return ($this->setPageOffset($query))->all();
    }

    







    private function getAllCitiesQuery(
        string  $regionCode,
        ?string $ariaCode,
        ?string $cityCode = null,
        bool    $selectQuery = false
    ): ActiveQuery {
        $query = Fias::find()
            ->andFilterWhere(['IN', 'region_code', $this->getAllRegionsQuery($regionCode, true)])
            ->andFilterWhere(['address_element_type' => AddressHelper::CITY_TYPE]);

        if (!EmptyCheck::isLoadingStringOrEmpty($ariaCode)) {
            $query->andFilterWhere(['IN', 'area_code', $this->getAllAreasQuery($regionCode, $ariaCode, true)]);
        } else {
            $query->andWhere(['area_code' => '0']);
        }

        if (!EmptyCheck::isLoadingStringOrEmpty($cityCode)) {
            $query->andWhere(['code' => $cityCode]);
        }
        if ($selectQuery) {
            $query->select('city_code');
        }
        return $query->orderBy([
            'name' => SORT_ASC,
            'code' => SORT_ASC
        ]);
    }

    





    public function getAllCities(string $regionCode, ?string $ariaCode): array
    {
        $areasQuery = $this->getAllCitiesQuery($regionCode, $ariaCode);

        $areasQuery = $this->addFilteringQuery($areasQuery);
        return ($this->setPageOffset($areasQuery))->all();
    }

    








    private function getAllVillagesQuery(
        string  $regionCode,
        ?string $ariaCode,
        ?string $cityCode,
        ?string $villageCode = null,
        bool    $selectQuery = false
    ): ActiveQuery {
        $query = Fias::find()
            ->andFilterWhere(['IN', 'region_code', $this->getAllRegionsQuery($regionCode, true)])
            ->andFilterWhere(['address_element_type' => AddressHelper::TOWN_TYPE]);

        if (!EmptyCheck::isLoadingStringOrEmpty($ariaCode)) {
            $query->andFilterWhere(['IN', 'area_code', $this->getAllAreasQuery($regionCode, $ariaCode, true)]);
        } else {
            $query->andWhere(['area_code' => '0']);
        }
        if (!EmptyCheck::isLoadingStringOrEmpty($cityCode)) {
            $query->andFilterWhere(['IN', 'city_code', $this->getAllCitiesQuery($regionCode, $ariaCode, $cityCode, true)]);
        } else {
            $query->andWhere(['city_code' => '0']);
        }

        if (!EmptyCheck::isLoadingStringOrEmpty($villageCode)) {
            $query->andWhere(['code' => $villageCode]);
        }
        if ($selectQuery) {
            $query->select('village_code');
        }
        return $query->orderBy([
            'name' => SORT_ASC,
            'code' => SORT_ASC
        ]);
    }

    






    public function getAllVillages(string $regionCode, ?string $ariaCode, ?string $cityCode): array
    {
        $query = $this->getAllVillagesQuery($regionCode, $ariaCode, $cityCode);

        $query = $this->addFilteringQuery($query);
        return ($this->setPageOffset($query))->all();
    }

    









    private function getAllStreetsQuery(
        string  $regionCode,
        ?string $ariaCode,
        ?string $cityCode,
        ?string $villageCode,
        ?string $streetCode = null,
        bool    $selectQuery = false
    ): ActiveQuery {
        $query = Fias::find()
            ->andFilterWhere(['IN', 'region_code', $this->getAllRegionsQuery($regionCode, true)])
            ->andFilterWhere(['address_element_type' => AddressHelper::STREET_TYPE]);

        if (!EmptyCheck::isLoadingStringOrEmpty($ariaCode)) {
            $query->andFilterWhere(['IN', 'area_code', $this->getAllAreasQuery($regionCode, $ariaCode, true)]);
        } else {
            $query->andWhere(['area_code' => '0']);
        }
        if (!EmptyCheck::isLoadingStringOrEmpty($cityCode)) {
            $query->andFilterWhere(['IN', 'city_code', $this->getAllCitiesQuery($regionCode, $ariaCode, $cityCode, true)]);
        } else {
            $query->andWhere(['city_code' => '0']);
        }
        if (!EmptyCheck::isLoadingStringOrEmpty($villageCode)) {
            $query->andFilterWhere(['IN', 'village_code', $this->getAllVillagesQuery($regionCode, $ariaCode, $cityCode, $villageCode, true)]);
        } else {
            $query->andWhere(['village_code' => '0']);
        }

        if (!EmptyCheck::isLoadingStringOrEmpty($streetCode)) {
            $query->andWhere(['code' => $streetCode]);
        }
        if ($selectQuery) {
            $query->select('street_code');
        }
        return $query->orderBy([
            'name' => SORT_ASC,
            'code' => SORT_ASC
        ]);
    }

    







    public function getAllStreets(string $regionCode, ?string $ariaCode, ?string $cityCode, ?string $villageCode): array
    {
        $query = $this->getAllStreetsQuery($regionCode, $ariaCode, $cityCode, $villageCode);

        $query = $this->addFilteringQuery($query);
        return ($this->setPageOffset($query))->all();
    }

    




    private function addFilteringQuery(ActiveQuery $query): ActiveQuery
    {
        if ($filterQuery = $this->request->post('filter_query')) {
            $query->andWhere([LikeQueryManager::getActionName(), 'name', $filterQuery]);
        }

        return $query;
    }

    




    private function setPageOffset(ActiveQuery $query): ActiveQuery
    {
        $page = $this->request->post('page', 1);
        $query->limit(AddressDataService::PAGE_SIZE)
            ->offset(AddressDataService::PAGE_SIZE * ($page - 1));

        return $query;
    }

    




    private function extractAttributeNameFromTemplate(string $textTemplate): array
    {
        $pattern = '/\{\{([^\}]+)\}\}/i';
        if (!preg_match_all($pattern, $textTemplate, $matches)) {
            return ['name'];
        }

        return array_unique($matches[1]);
    }

    





    private function buildReplacePairsForStrtr(string $textTemplate, Fias $data): array
    {
        $attributeList = $this->extractAttributeNameFromTemplate($textTemplate);

        $replacePairs = [];
        foreach ($attributeList as $attribute) {
            $replacePairs["{{{$attribute}}}"] = ArrayHelper::getValue($data, $attribute);
        }

        return $replacePairs;
    }
}
