<?php

namespace common\modules\student\controllers;

use backend\models\SortedElementPage;
use common\models\User;
use frontend\modules\user\models\LoginForm;
use Yii;
use yii\filters\AccessControl;

class StudentController extends \yii\web\Controller
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

    public function actionIndex()
    {
        



        $updateUser = new LoginForm();
        $updateUser->SetRecordbooks(Yii::$app->user->identity->userRef->reference_id);
        

        if (SortedElementPage::checkIfNeedUpdate(User::ROLE_STUDENT)) {
            SortedElementPage::updateElements(User::ROLE_STUDENT);
        }
        $routesList = SortedElementPage::getAllSortedRoutes(User::ROLE_STUDENT);
        return $this->render(
            '@common/modules/student/views/student/index',
            $routesList
        );
    }
}
