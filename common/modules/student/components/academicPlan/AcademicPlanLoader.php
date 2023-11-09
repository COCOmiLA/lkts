<?php

namespace common\modules\student\components\academicPlan;

use common\models\User;
use common\modules\student\components\academicPlan\models\AcademicPlan;
use Yii;
use yii\base\Component;

class AcademicPlanLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{

    public $serviceUrl;
    public $specialtyServiceUrl;
    public $plansServiceUrl;
    public $semesterServiceUrl;

    public $login;
    public $password;

    protected $client;

    public $guid;


    public $componentName = "Учебные планы";
    public $baseRoute = 'student/academicplan';

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    public function isAllowedToRole($role)
    {
        switch ($role) {
            case (User::ROLE_TEACHER):
            case (User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }


    public static function getConfig()
    {
        return [
            'class' => \common\modules\student\components\academicPlan\AcademicPlanLoader::class,
            'serviceUrl' => getenv('SERVICE_URI') . 'StudentsPlan/PlanLoad/PlanLoad',
            'specialtyServiceUrl' => getenv('SERVICE_URI') . 'StudentsPlan/Specialties/Specialties',
            'plansServiceUrl' => getenv('SERVICE_URI') . 'StudentsPlan/Plans/Plans',
            'semesterServiceUrl' => getenv('SERVICE_URI') . 'StudentsPlan/PlanPeriods/PlanPeriods',
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\AcademicplanController';
    }

    public static function getUrlRules()
    {
        return [
            'student/academicplan' => 'academicplan/index',
            'student/plans' => 'academicplan/plans',
            'student/semesters' => 'academicplan/semesters'
        ];
    }

    public function setParams($guid)
    {
        if ($this->checkParams($guid)) {
            $this->guid = $guid;

            return true;
        } else {
            return false;
        }
    }

    public function checkParams($guid)
    {
        return true;
    }

    public function getUserSpecIds($guid)
    {
        $this->setParams('');
        return [];
    }

    public function loadSemesters($plan_id)
    {
        if ($this->checkParams($this->guid) && $plan_id != null && $plan_id != 'Загрузка ...') {
            $formatedData = [];

            $response = Yii::$app->soapClientStudent->load(
                'GetCurriculumTerms',
                [
                    'CurriculumId' => $plan_id
                ]
            );

            if ($response === false) {
                return null;
            }

            if (isset($response->return->Term) && $response->return->Term != null) {
                $formatedData = $this->BuildSemesterFromXML($response->return->Term, $plan_id);
            }

            return $formatedData;
        } else {
            return null;
        }
    }

    public function loadDisciplines($plan_id, $semester_id)
    {
        if ($this->checkParams($this->guid) && $plan_id != null) {
            $formatedData = [];

            $response = Yii::$app->soapClientStudent->load(
                'GetCurriculumLoad',
                [
                    'CurriculumId' => $plan_id,
                    'TermId' => $semester_id,
                ]
            );

            if ($response === false) {
                return null;
            }

            if (isset($response->return->CurriculumLoad) && $response->return->CurriculumLoad != null) {
                $formatedData = $this->BuildDisciplinesFromXML($response->return->CurriculumLoad, $plan_id, $semester_id);
            }

            return $formatedData;
        } else {
            return null;
        }
    }

    public function loadPlans()
    {
        if ($this->checkParams($this->guid)) {
            if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)) {
                $recordbooks = Yii::$app->getPortfolioService->loadRawRecordbooks(Yii::$app->user->identity->userRef->reference_id);

                return $this->BuildPlansFromXML($recordbooks);
            } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)) {

                $user_info = Yii::$app->getPortfolioService->loadReference(
                    [
                        'Parameter' => Yii::$app->user->identity->guid,
                        'ParameterType' => 'Код',
                        'ParameterRef' => 'Справочник.ФизическиеЛица'
                    ]
                );
                $user = json_decode(json_encode($user_info->return->Reference), true);
                $states = Yii::$app->getPortfolioService->loadEmployerStates(['PersonRef' => $user]);
                $all_curriculums = [];
                if (isset($states->return, $states->return->EmployerState)) {
                    $_states = $states->return->EmployerState;
                    if (!is_array($_states)) {
                        $_states = [$_states];
                    }
                    $_curriculums = [];
                    foreach ($_states as $state) {
                        if (!empty($state)) {
                            $curriculums = Yii::$app->getPortfolioService->loadEmployersCurriculums([
                                'EmployerRef' => $user,
                                'EmployerState' => json_decode(json_encode($state), true)
                            ]);
                            if (isset($curriculums->return, $curriculums->return->Curriculum)) {
                                $_curriculums = $curriculums->return->Curriculum;
                            }
                        }
                        if (!is_array($_curriculums)) {
                            $_curriculums = [$_curriculums];
                        }
                        $all_curriculums = array_merge($all_curriculums, $_curriculums);
                    }
                }

                $format_all_curriculums = [];
                $duplicateCheckerArray = [];
                foreach ($all_curriculums as $curriculum) {
                    if (!in_array($curriculum->CurriculumRef->ReferenceUID, $duplicateCheckerArray)) {
                        $format_all_curriculums[] = (object)[
                            'id' => $curriculum->CurriculumId,
                            'name' => $curriculum->CurriculumName,
                        ];
                        $duplicateCheckerArray[] = $curriculum->CurriculumRef->ReferenceUID;
                    }
                }
                return $format_all_curriculums;
            }
        }
        return null;
    }

    protected function buildUrl($type)
    {
        $urlTemplate = "";
        $url = null;
        switch ($type) {
            case (0):
                $url = $this->specialtyServiceUrl;
                break;
            case (1):
                $url = $this->plansServiceUrl;
                break;
            case (2):
                $url = $this->serviceUrl;
                break;
            case (3):
                $url = $this->semesterServiceUrl;
        }
        if (substr($url, -1) != '/') {
            $urlTemplate = $url . '/';
        } else {
            $urlTemplate = $url;
        }

        $url = $urlTemplate;

        return $url;
    }

    protected function BuildSpecialitiesFromXML($data)
    {
        $xml_spec = $data;
        $specialities = [];
        if (is_array($xml_spec)) {
            foreach ($xml_spec as $spec) {
                $specialty = new models\Specialty;
                $specialty->id = $spec->CurriculumId;
                $specialty->name = $spec->SpecialtyName;
                $specialities[] = $specialty;
            }
        } else {
            $spec = $xml_spec;
            $specialty = new models\Specialty;
            $specialty->id = $spec->RecordbookId;
            $specialty->name = $spec->SpecialtyName;
            $specialities[] = $specialty;
        }

        return $specialities;
    }

    protected function setPlanName($plan, $xml_plan): void
    {
        if (!str_contains((string)$plan->name, 'Зачетная книжка №')) {
            if (!str_contains((string)$xml_plan->CurriculumName, 'Зачетная книжка №')) {
                $plan->name = "Зачетная книжка №{$xml_plan->RecordbookName}. {$xml_plan->CurriculumName}";
            } else {
                $plan->name = $xml_plan->CurriculumName;
            }
        }
    }

    protected function BuildPlansFromXML($data)
    {
        $xml_plans = $data;
        $plans = [];
        if (is_array($xml_plans)) {
            foreach ($xml_plans as $xml_plan) {
                $plan = new AcademicPlan();
                $plan->id = $xml_plan->CurriculumId;
                $this->setPlanName($plan, $xml_plan);
                $plans[] = $plan;
            }
        } else {
            $xml_plan = $xml_plans;
            $plan = new AcademicPlan();
            $plan->id = $xml_plan->CurriculumId;
            $this->setPlanName($plan, $xml_plan);
            $plans[] = $plan;
        }
        return $plans;
    }

    protected function BuildDisciplinesFromXML($data, $plan_id, $semester_id)
    {
        $xml_disciplines = $data;
        $disciplines = [];
        if (is_array($xml_disciplines)) {
            foreach ($xml_disciplines as $xml_discipline) {
                $discipline = new models\Discipline();
                $discipline->unit = $xml_discipline->Unit;
                $discipline->period = $xml_discipline->Term;
                $discipline->name = $xml_discipline->Subject;
                $discipline->load = $xml_discipline->LoadType;
                $discipline->amount = $xml_discipline->Amount;
                $discipline->IsControl = $xml_discipline->IsControl;
                $discipline->code = $xml_discipline->PropertiesUMK[1]->Value->ReferenceId;
                $disciplines[] = $discipline;
            }
        } else {
            $xml_discipline = $xml_disciplines;
            $discipline = new models\Discipline();
            $discipline->unit = $xml_discipline->Unit;
            $discipline->period = $xml_discipline->Term;
            $discipline->name = $xml_discipline->Subject;
            $discipline->load = $xml_discipline->LoadType;
            $discipline->amount = $xml_discipline->Amount;
            $discipline->IsControl = $xml_discipline->IsControl;
            $discipline->code = $xml_discipline->PropertiesUMK[1]->Value->ReferenceId;
            $disciplines[] = $discipline;
        }

        return $disciplines;
    }

    protected function BuildSemesterFromXML($data, $plan_id)
    {
        $xml_semesters = $data;
        $semesters = [];
        if (is_array($xml_semesters)) {
            foreach ($xml_semesters as $xml_semester) {
                $semester = new models\Semester();
                $semester->name = $xml_semester->TermName;
                $semester->id = $xml_semester->TermId;

                $semesters[] = $semester;
            }
        } else {
            $xml_semester = $xml_semesters;
            $semester = new models\Semester();
            $semester->name = $xml_semester->TermName;
            $semester->id = $xml_semester->TermId;

            $semesters[] = $semester;
        }

        return $semesters;
    }
}
