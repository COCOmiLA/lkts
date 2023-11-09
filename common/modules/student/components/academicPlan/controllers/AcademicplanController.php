<?php

namespace common\modules\student\components\academicPlan\controllers;

use Yii;
use yii\filters\AccessControl;

class AcademicplanController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'plans', 'semesters'],
                        'allow' => true,
                        'roles' => ['student', 'teacher']
                    ],
                ]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }

    public $plan_id;
    public $semester_id;
    public $plans;
    public $semesters;
    public $disciplines;

    public function academicPlan($plan_id, $semester_id)
    {
        $plan = null;
        $firstRound = true;
        $this->plan_id = $plan_id;
        $this->semester_id = $semester_id;
        $this->disciplines = [];

        while ($firstRound || $plan == '401') {
            $academicPlanLoader = Yii::$app->getModule('student')->academicPlanLoader;
            $academicPlanLoader->setParams('');

            $this->plans = $academicPlanLoader->loadPlans();
            if ($this->plan_id == null && sizeof($this->plans) > 0) {
                $this->plan_id = $this->plans[0]->id;
            }

            $this->semesters = $academicPlanLoader->loadSemesters($this->plan_id);

            if (isset($semester_id)) {
                $this->disciplines = $academicPlanLoader->loadDisciplines($this->plan_id, $this->semester_id);
            }

            if ($firstRound) {
                $firstRound = false;
            }
        }
    }

    public function actionIndex()
    {
        $plan_id = null;
        $semester_id = null;

        if (Yii::$app->request->isPost) {
            $plan_id = Yii::$app->request->post('plan_plan');
            $semester_id = Yii::$app->request->post('plan_semester');
            
            $_GET['sort'] = Yii::$app->request->post('sort');
            
        }
        $this->academicPlan($plan_id, $semester_id);
        return $this->render(
            '@common/modules/student/components/academicPlan/views/academicPlan',
            [
                'plans' => $this->plans,
                'plan_id' => $this->plan_id,
                'semesters' => $this->semesters,
                'semester_id' => $this->semester_id,
                'disciplines' => $this->disciplines,
            ]
        );
    }

    public function actionPlans()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null && $parents[0] != '') {
                $parent_id = (string)$parents[0];

                $loader = Yii::$app->getModule('student')->academicPlanLoader;
                $user_spec_ids = $loader->getUserSpecIds(Yii::$app->user->identity->guid);
                $loader->setParams('');
                if (in_array($parent_id, $user_spec_ids)) {
                    $loader->setParams(Yii::$app->user->identity->guid);
                }
                $plans = $loader->loadPlans($parent_id);
                $plans_array = null;
                if (is_array($plans)) {
                    $plans_array = array_map(function ($o) {
                        return ["id" => $o->id, "name" => $o->name];
                    }, $plans);
                }

                $output = [];
                if (sizeof($plans_array > 0)) {
                    foreach ($plans_array as $pa) {
                        if (strpos($pa['name'], $parent_id)) {
                            array_push($output, $pa);
                        }
                    }
                }

                $selected = '';
                if (sizeof($output) > 0) {
                    $selected = $output[0]['id'];
                }

                return json_encode(['output' => $output, 'selected' => $selected]);
            } else {
                return json_encode(['output' => '', 'selected' => '']);
            }
        } else {
            return json_encode(['output' => '', 'selected' => '']);
        }
    }

    public function actionSemesters()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null && $parents[0] != '') {
                $parent_id = (string)$parents[0];

                $loader = Yii::$app->getModule('student')->academicPlanLoader;
                $loader->setParams('');

                $semesters = $loader->loadSemesters($parent_id);
                $semesters_array = null;
                if (is_array($semesters)) {
                    $semesters_array = array_map(function ($o) {
                        return ["id" => $o->id, "name" => $o->name];
                    }, $semesters);
                }
                return json_encode(['output' => $semesters_array, 'selected' => '']);
            } else {
                return json_encode(['output' => '', 'selected' => '']);
            }
        } else {
            return json_encode(['output' => '', 'selected' => '']);
        }
    }
}