<?php

namespace common\modules\student\components\umk\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\Cors;

class UmkController extends \yii\web\Controller
{

    public $specialty;
    public $plans;
    public $semesters;
    public $disciplines;
    public $semester_id;

    public $discipline_id;
    public $specialty_id;
    public $plan_id;

    public $role;


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'discipline',
                            'discipline-caf',
                        ],
                        'allow' => true,
                        'roles' => [
                            User::ROLE_STUDENT,
                            User::ROLE_TEACHER,
                        ]
                    ],
                ]
            ],
            'corsFilter' => ['class' => Cors::class],
        ];
    }

    public function beforeAction($action)
    {
        if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
            $this->role = User::ROLE_STUDENT;
        } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
            $this->role = User::ROLE_TEACHER;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex($luid = '', $puid = '', $plan_plan = null)
    {
        $treeArray = [];

        $portfolioLoader = Yii::$app->getModule('student')->portfolioLoader;

        $recordbook_id = '';

        if ($this->role == User::ROLE_STUDENT) {
            $plan = null;
            $firstRound = true;
            while ($firstRound) {
                $academicPlanLoader = Yii::$app->getModule('student')->academicPlanLoader;
                $academicPlanLoader->setParams('');

                $this->specialty_id = Yii::$app->request->get('plan_specialty');
                $this->plan_id = Yii::$app->request->get('plan_plan');
                $this->semester_id = Yii::$app->request->get('plan_semester');
                $this->discipline_id = Yii::$app->request->get('plan_discipline');
                $this->plans = $academicPlanLoader->loadPlans();
                if (!empty($plan_plan)) {
                    $this->plan_id = $plan_plan;
                } else {
                    $this->plan_id = $this->plans[0]->id;
                }
                $this->semesters = $academicPlanLoader->loadSemesters($this->plan_id);
                if ($this->semester_id == null && sizeof($this->semesters) > 0) {
                    $this->semester_id = $this->semesters[0]->id;
                }
                $discipline_info = null;
                if (!empty($this->semester_id) && !empty($this->plan_id)) {
                    $disciplines = Yii::$app->getPortfolioService->loadDisciplines($this->plan_id, $this->semester_id);

                    $_loads = [];
                    if (isset($disciplines->return->CurriculumLoad)) {
                        $_loads = is_array($disciplines->return->CurriculumLoad) ? $disciplines->return->CurriculumLoad : [$disciplines->return->CurriculumLoad];
                    }

                    $loads = array_map(function ($o) {
                        return ["id" => $o->UMKOwnerId, "name" => $o->Subject];
                    }, $_loads);
                    $this->disciplines = array_unique($loads, SORT_REGULAR);
                }
                $this->discipline_id = Yii::$app->request->get('plan_discipline');
                if ($this->discipline_id != null) {
                    foreach ($_loads as $discipline) {
                        if ($discipline->UMKOwnerId == $this->discipline_id) {
                            $discipline_info = $discipline->PropertiesUMK;
                            break;
                        }
                    }
                }
                if ($firstRound) {
                    $firstRound = false;
                }
            }
            $recordbook_id = null;
        }

        if ($this->role == User::ROLE_TEACHER) {
            $user_info = Yii::$app->getPortfolioService->loadReference(
                [
                    'Parameter' => Yii::$app->user->identity->guid,
                    'ParameterType' => 'Код',
                    'ParameterRef' => 'Справочник.ФизическиеЛица'
                ]
            );
            $states = Yii::$app->getPortfolioService->loadEmployerStates([
                'PersonRef' => json_decode(json_encode($user_info->return->Reference), true)
            ]);
            $_states = $states->return->EmployerState;
            if (!is_array($_states)) {
                $_states = [$_states];
            }

            if (empty($plan_plan)) {
                $plan_plan = 0;
            }
            $this->plan_id = $plan_plan;
            $this->plans = [];
            foreach ($_states as $i => $state) {
                $this->plans[] = ['id' => $i, 'name' => $state->JobDescription];
            }

            if (isset($_states[$plan_plan])) {
                $empState = json_decode(json_encode($_states[$plan_plan]), true);
                unset($empState[$plan_plan]['PropertyEmployerState']);
                $umk = Yii::$app->getPortfolioService->loadEmployerUMK([
                    'EmployerState' => $empState
                ]);
            } else {
                $umk = '';
            }
            $disciplines = [];
            if (!empty($umk) && isset($umk->return, $umk->return->UMKStrings)) {

                $umk_strings = $umk->return->UMKStrings;
                if (!is_array($umk_strings))
                    $umk_strings = [$umk_strings];

                $this->disciplines = array_map(function ($item) {
                    return [
                        'name' => $item->PropertiesUMK[0]->Value->ReferenceName,
                        'id' => $item->PropertiesUMK[0]->Value->ReferenceId
                    ];
                }, $umk_strings);
            }
            $this->discipline_id = Yii::$app->request->get('plan_discipline');
            $discipline_info = null;
            $treeArray = [];
            if (!empty($umk) && isset($umk->return, $umk->return->UMKStrings))
                foreach ($umk->return->UMKStrings as $UMKString) {
                    if (
                        isset($UMKString->PropertiesUMK, $UMKString->PropertiesUMK[0], $UMKString->PropertiesUMK[0]->Value, $UMKString->PropertiesUMK[0]->Value->ReferenceId) &&
                        $UMKString->PropertiesUMK[0]->Value->ReferenceId == $this->discipline_id
                    ) {
                        $discipline_info = $UMKString->PropertiesUMK;
                        break;
                    }
                }
        }

        if (!empty($discipline_info)) {

            $planTrees = $portfolioLoader->loadPlanTree(
                $this->discipline_id,
                'Дисциплины',
                ['PropertyStrings' => $discipline_info]
            );

            foreach ($planTrees as $planTree) {
                $treeArray[] = Yii::$app->treeParser->parseTree($planTree, $puid, $luid);
            }
        }
        $portfolio = $portfolioLoader->loadLapResults($puid, $luid);
        $files = [];
        if (isset($portfolio->return, $portfolio->return->LapResultStrings)) {
            $lapStrings = $portfolio->return->LapResultStrings;
            if (!empty($lapStrings) && !is_array($lapStrings))
                $lapStrings = [$lapStrings];

            foreach ($lapStrings as $port) {
                $files[$port->Result->ReferenceUID] = $portfolioLoader->loadAttachedFileList($port->Result->ReferenceUID, 'Справочник.Объекты');
            }
        }
        return $this->render('@common/modules/student/components/umk/views/umk', [
            'treeArray' => $treeArray,
            'portfolio' => $portfolio,

            'files' => $files,
            'recordbook_id' => $recordbook_id,

            'luid' => $luid,
            'puid' => $puid,

            'plans' => $this->plans,
            'disciplines' => $this->disciplines,
            'semesters' => $this->semesters,
            'plan_id' => $this->plan_id,

            'semester_id' => $this->semester_id,
            'discipline_id' => $this->discipline_id,

            'specialty_id' => $this->specialty_id,

            'role' => $this->role
        ]);
    }

    public function actionDiscipline()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params))
            return json_encode(['output' => [], 'selected' => '']);

        $plan_id = $params['plan_id'];
        $semester_id = $params['semester_id'];

        if ($semester_id == 'Загрузка ...')
            return json_encode(['output' => [], 'selected' => '']);

        $disciplines = Yii::$app->getPortfolioService->loadDisciplines($plan_id, $semester_id);

        $_loads = [];
        if (isset($disciplines->return->CurriculumLoad)) {
            $_loads = is_array($disciplines->return->CurriculumLoad) ? $disciplines->return->CurriculumLoad : [$disciplines->return->CurriculumLoad];
        }

        $loads = array_map(function ($o) {
            return ["id" => $o->UMKOwnerId, "name" => $o->Subject];
        }, $_loads);


        return json_encode(['output' => array_unique($loads, SORT_REGULAR), 'selected' => '']);
    }

    public function actionDisciplineCaf()
    {
        $params = Yii::$app->request->post('depdrop_all_params');

        if (empty($params))
            return json_encode(['output' => [], 'selected' => '']);

        $plan_id = $params['plan_id'];


        $user_info = Yii::$app->getPortfolioService->loadReference(
            [
                'Parameter' => Yii::$app->user->identity->guid,
                'ParameterType' => 'Код',
                'ParameterRef' => 'Справочник.ФизическиеЛица'
            ]
        );


        $states = Yii::$app->getPortfolioService->loadEmployerStates([
            'PersonRef' => json_decode(json_encode($user_info->return->Reference), true)
        ]);


        $_states = $states->return->EmployerState;

        if (!is_array($_states)) {
            $_states = [$_states];
        }


        foreach ($_states as $i => $state) {
            $this->plans[] = ['id' => $i, 'name' => $state->JobDescription];
        }

        if (isset($_states[$plan_id])) {

            $empState = json_decode(json_encode($_states[$plan_id]), true);
            unset($empState[$plan_id]['PropertyEmployerState']);

            $umk = Yii::$app->getPortfolioService->loadEmployerUMK([
                'EmployerState' => $empState
            ]);
        } else {
            $umk = '';
        }

        $disciplines = [];

        if (isset($umk->return, $umk->return->UMKStrings)) {

            $umk_strings = $umk->return->UMKStrings;
            if (!is_array($umk_strings))
                $umk_strings = [$umk_strings];

            $disciplines = array_map(function ($item) {
                return [
                    'name' => $item->PropertiesUMK[0]->Value->ReferenceName,
                    'id' => $item->PropertiesUMK[0]->Value->ReferenceId
                ];
            }, $umk_strings);
        }


        return json_encode(['output' => array_unique($disciplines, SORT_REGULAR), 'selected' => '']);
    }
}
