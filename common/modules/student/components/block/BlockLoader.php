<?php

namespace common\modules\student\components\block;

use common\models\User;
use common\modules\student\components\block\models\CourseUnit;
use common\modules\student\components\block\models\CreateIndividualResult;
use common\modules\student\components\block\models\Subject;
use common\modules\student\models\ResultType;
use stdClass;
use Yii;
use yii\helpers\ArrayHelper;

class BlockLoader extends \yii\base\Component implements
    \common\modules\student\interfaces\DynamicComponentInterface,
    \common\modules\student\interfaces\RoutableComponentInterface
{
    public $userId;

    public $login;
    public $password;

    public $serviceUrl;

    protected $client;

    public $componentName = "Запись на курсы по выбору";
    public $baseRoute = 'student/block';

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
            case (User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }

    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\block\blockLoader',
            'serviceUrl' => getenv('SERVICE_URI') . 'Students/Block/',
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\BlockController';
    }

    public static function getUrlRules()
    {
        return [
            'student/block' => 'block/index',
        ];
    }


    public function setParams($userId)
    {
        if ($userId != '') {
            $this->userId = $userId;

            return true;
        } else {
            return false;
        }
    }

    public function CreateIndividualEducationPredmets($recordbook_id, $data): CreateIndividualResult
    {
        if (!$data) {
            return CreateIndividualResult::fromRaw(null);
        }

        $response = Yii::$app->soapClientStudent->load(
            'CreateIndividualEducationPredmets',
            [
                'UserId' => $this->userId,
                'RecordbookId' => $recordbook_id,
                'CourseUnits' => [
                    'CourseUnit' => $data
                ]
            ]
        );

        return CreateIndividualResult::fromRaw($response->return ?? null);
    }

    public function CheckCount($course_unit)
    {
        if (!$course_unit->Variable) { 
            return true;
        }

        $count = [];
        foreach ($course_unit->SubjectsString['Subject'] as $subject) {
            if ($subject->Checked && array_search($subject->SubjectId, $count) === false) {
                $count[] = $subject->SubjectId;
            }
        }
        $count = count($count);

        if ($count === $course_unit->MaxCount) { 
            return true;
        } else {
            return false;
        }
    }

    public function loadList()
    {
        $responseData = [];

        $recordbooks = Yii::$app->getPortfolioService->loadRawRecordbooks(Yii::$app->user->identity->userRef->reference_id);

        $units_error = false;

        $errors = [];

        if (Yii::$app->request->isPost) {
            $Subjects = Yii::$app->request->post('block');

            $data = [];

            if (sizeof($Subjects) > 0) {
                $s_key = key($Subjects);
                $subject = explode('|', base64_decode($s_key));
                $subject_value = '1';
                unset($Subjects[$s_key]);

                $recordbook_index = $subject[0];

                $course_unit = new CourseUnit($subject);
                array_push($course_unit->SubjectsString['Subject'], new Subject($subject, $subject_value));

                foreach ($Subjects as $subject_key => $subject_value) {
                    $subject = explode('|', base64_decode($subject_key));

                    
                    if ($course_unit->CourseUnitId === $subject[1] && $course_unit->CourseUnitName === $subject[2]) {
                        array_push($course_unit->SubjectsString['Subject'], new Subject($subject, $subject_value));
                    } else { 
                        if ($this->CheckCount($course_unit)) {
                            $data[] = $course_unit;
                        } else {
                            $units_error = true;
                        }

                        $course_unit = new CourseUnit($subject);
                        array_push($course_unit->SubjectsString['Subject'], new Subject($subject, $subject_value));
                    }

                    if ($recordbook_index !== $subject[0]) {
                        $response = $this->CreateIndividualEducationPredmets($recordbooks[(int)$recordbook_index]->RecordbookId, $data);

                        if ($response->result === ResultType::FAIL) {
                            $errors[] = \Yii::t('common', 'При отправке данных произошла ошибка.');
                        }

                        $errors = array_merge($errors, ArrayHelper::getColumn($response->error, 'description'));

                        $data = [];

                        $recordbook_index = $subject[0];
                    }
                }

                if ($this->CheckCount($course_unit)) {
                    $data[] = $course_unit;
                } else {
                    $units_error = true;
                }

                $response = $this->CreateIndividualEducationPredmets($recordbooks[(int)$recordbook_index]->RecordbookId, $data);

                if ($response->result === ResultType::FAIL) {
                    $errors[] = \Yii::t('common', 'При отправке данных произошла ошибка.');
                }

                $errors = array_merge($errors, ArrayHelper::getColumn($response->error, 'description'));
            }
        }

        if (!is_array($recordbooks)) {
            $recordbooks = [$recordbooks];
        }

        foreach ($recordbooks as $recordbook) {
            $response = Yii::$app->soapClientStudent->load(
                'GetIndividualEducationPredmets',
                [
                    'UserId' => $this->userId,
                    'RecordbookId' => $recordbook->RecordbookId
                ]
            );

            if ($response === false) {
                continue;
            }

            if (isset($response->return->CourseUnit) && $response->return->CourseUnit != null) {
                $data = [];

                $courseUnitFrom1C = $response->return->CourseUnit;
                if (!is_array($courseUnitFrom1C)) {
                    $courseUnitFrom1C = [$courseUnitFrom1C];
                }

                foreach ($courseUnitFrom1C as $CourseUnit) {
                    $course_unit = new CourseUnit($CourseUnit);

                    if (!is_array($CourseUnit->SubjectsString->Subject)) {
                        $CourseUnit->SubjectsString->Subject = [$CourseUnit->SubjectsString->Subject];
                    }
                    usort($CourseUnit->SubjectsString->Subject, function (stdClass $a, stdClass $b) {
                        
                        return $a->SubjectName <=> $b->SubjectName;
                    });

                    foreach ($CourseUnit->SubjectsString->Subject as $subject) {
                        $course_unit->SubjectsString[] = new Subject($subject, null);
                    }

                    $data[] = $course_unit;
                }

                $responseData[$recordbook->SpecialtyName] = $data;
            }
        }

        if (!empty($errors)) {
            Yii::$app->session->setFlash('individualEducationPredmetsErrors', $errors);
        }

        return [$responseData, $units_error];
    }
}
