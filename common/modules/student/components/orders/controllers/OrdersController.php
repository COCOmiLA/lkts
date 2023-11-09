<?php

namespace common\modules\student\components\orders\controllers;

use yii\filters\AccessControl;

class OrdersController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['student']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex(){
        return $this->render('@common/modules/student/components/orders/views/order');
    }

}