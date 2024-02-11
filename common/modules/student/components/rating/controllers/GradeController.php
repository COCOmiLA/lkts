<?php

namespace common\modules\student\components\rating\controllers;

use common\models\User;
use yii\filters\AccessControl;

class GradeController extends \yii\web\Controller
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
                        'roles' => [User::ROLE_STUDENT]
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('@common/modules/student/components/grade/views/grade');
    }

}