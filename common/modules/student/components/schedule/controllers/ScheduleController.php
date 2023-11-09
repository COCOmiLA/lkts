<?php

namespace common\modules\student\components\schedule\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class ScheduleController extends \yii\web\Controller
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
                        'roles' => [User::ROLE_TEACHER, User::ROLE_STUDENT],
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public function actionIndex(
        $scheduleType = null,
        $objectType = null,
        $objectId = null,
        $startDate = null,
        $endDate = null,
        $day_button = null,
        $another_group = '',
        $selected_position = null
    )
    {
        if ($scheduleType == null) {
            $scheduleType = 'Week';
        }

        if ($objectType == null) {
            if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)) {
                $objectType = 'AcademicGroup';
            } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)) {
                $objectType = 'Teacher';
            }
        }

        if ($objectId == null) {
            $objectId = '0';
            if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)) {
                $objectId = ArrayHelper::getValue(Yii::$app->user->identity, 'userRef.reference_id');
            }
        }

        $day = date('w');
        $week_start = date('d.m.Y', strtotime('-' . ($day - 1) . ' days'));
        $week_end = date('d.m.Y', strtotime('+' . (7 - $day) . ' days'));

        if ($startDate == null) {
            $startDate = $week_start;
        }
        if ($endDate == null) {
            $endDate = $week_end;
        }

        return $this->render('@common/modules/student/components/schedule/views/schedule', [
            'scheduleType' => $scheduleType,
            'objectType' => $objectType,
            'objectId' => $objectId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'day_button' => $day_button,
            'another_group' => $another_group,
            'selected_position' => $selected_position,
        ]);
    }

}