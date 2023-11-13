<?php

namespace common\modules\student\controllers;

use backend\models\SortedElementPage;
use common\models\User;
use yii\filters\AccessControl;

class TeacherController extends \yii\web\Controller
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
                        'roles' => ['teacher']
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
        if (SortedElementPage::checkIfNeedUpdate(User::ROLE_TEACHER)) {
            SortedElementPage::updateElements(User::ROLE_TEACHER);
        }
        $routesList = SortedElementPage::getAllSortedRoutes(User::ROLE_TEACHER);
        return $this->render(
            '@common/modules/student/views/student/index',
            $routesList
        );
    }
}
