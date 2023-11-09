<?php

namespace common\modules\student\components\block\controllers;

use yii\filters\AccessControl;
use yii\filters\Cors;

class BlockController extends \yii\web\Controller
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
                'class' => Cors::class,
            ],
        ];
    }

    public function actionIndex(){
        return $this->render('@common/modules/student/components/block/views/block');
    }
}