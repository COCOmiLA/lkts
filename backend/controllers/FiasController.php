<?php

namespace backend\controllers;

use backend\components\KladrLoader;
use common\models\User;
use yii\filters\AccessControl;

class FiasController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    private function getRegionsArrayFromHash(array $regions_hash): array
    {
        $regions = [];
        foreach ($regions_hash as $number => $region) {
            $regions[] = [
                'number' => $number,
                'name' => $region
            ];
        }
        return $regions;
    }

    public function actionIndex()
    {
        $regions = KladrLoader::fetchRegionList();
        $regions = $this->getRegionsArrayFromHash($regions);
        $regions_data_provider = new \yii\data\ArrayDataProvider([
            'allModels' => $regions,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        return $this->render('index', ['dataProvider' => $regions_data_provider]);
    }

    public function actionLoadRegion()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $region_number = \Yii::$app->request->post('region_number');
        try {
            KladrLoader::loadRegion($region_number);
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'region' => $region_number
            ];
        }
        return [
            'status' => true,
            'region' => $region_number
        ];
    }
}