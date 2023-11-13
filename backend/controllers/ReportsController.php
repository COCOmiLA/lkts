<?php

namespace backend\controllers;

use backend\models\SummaryDate;
use common\models\User;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class ReportsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['post']]
            ],
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

    public function actionSummary()
    {
        [
            'totalCount' => $totalCount,
            'summaryDateAll' => $summaryDateAll,
        ] = SummaryDate::findAllAndCountTotal();
        $summaryDatesProvider = new ArrayDataProvider(
            [
                'allModels' =>  $summaryDateAll,
                'sort' => [
                    'defaultOrder' => ['timestamp' => SORT_ASC],
                    'attributes' => [
                        'new_users',
                        'timestamp',
                        'new_applications',
                        'sended_applications',
                        'approved_applications',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 30,
                ],
            ]
        );

        return $this->render(
            'summary',
            [
                'total' => $totalCount,
                'summaryDatesProvider' => $summaryDatesProvider,
            ]
        );
    }
}
