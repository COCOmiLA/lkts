<?php

namespace common\modules\student\components\graduateWork\controllers;


use Yii;
use yii\filters\AccessControl;

class GraduateworkController extends \yii\web\Controller
{

    public $graduateWorkLoader;
    public $graduateWorks;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['student','teacher']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex() {
        $recordBook_id = null;
        if (Yii::$app->request->isPost) {
            $recordBook_id = Yii::$app->request->post('record_book');
        }

        return $this->render('@common/modules/student/components/graduateWork/views/graduateWork',
            [
                'recordBook_id' => $recordBook_id
            ]);
    }
}