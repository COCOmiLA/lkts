<?php

namespace common\modules\student\components\stipend\controllers;

use Yii;
use yii\filters\AccessControl;

class StipendController extends \yii\web\Controller
{

    public $stipendLoader;
    public $stipends;

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

    public function actionIndex(){
        $recordBook_id = null;
        if (Yii::$app->request->isPost) {
            $recordBook_id = Yii::$app->request->post('record_book');
        }

        return $this->render('@common/modules/student/components/stipend/views/stipend',
            [
                'recordBook_id' => $recordBook_id
            ]);
    }
}