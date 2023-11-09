<?php

namespace common\modules\student\components\schedule;

use common\models\EmptyCheck;
use common\models\User;
use common\modules\student\components\schedule\models\ScheduleClassroom;
use common\modules\student\components\schedule\models\ScheduleDay;
use common\modules\student\components\schedule\models\ScheduleGroup;
use common\modules\student\components\schedule\models\ScheduleLesson;
use common\modules\student\components\schedule\models\ScheduleObject;
use common\modules\student\components\schedule\models\ScheduleProject;
use common\modules\student\components\schedule\models\ScheduleTeacher;
use common\modules\student\components\schedule\models\ScheduleWeek;
use common\modules\student\interfaces\DynamicComponentInterface;
use common\modules\student\interfaces\RoutableComponentInterface;
use SimpleXMLElement;
use stdClass;
use Throwable;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class ScheduleLoader extends Component
    implements DynamicComponentInterface, RoutableComponentInterface
{
    const DATA_TO_WEB = 'd.m.Y';
    const DATA_TO_1C = 'Y-m-d';

    public $objectType;
    public $objectId;
    public $scheduleType;
    public $scheduleStartDate;
    public $scheduleEndDate;
    public $userRef;
    public $another_group;

    public $login;
    public $password;

    protected $client;

    public $params;

    public $componentName = 'Расписание';
    public $baseRoute = 'student/schedule';

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
            'class' => 'common\modules\student\components\schedule\ScheduleLoader',
            'login' => getenv('STUDENT_LOGIN'),
            'password' => getenv('STUDENT_PASSWORD'),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\ScheduleController';
    }

    public static function getUrlRules()
    {
        return ['student/schedule' => 'schedule/index'];
    }

    public function setParams($objectType, $objectId, $scheduleType, $scheduleStartDate, $scheduleEndDate, $userRef, $anothe_group)
    {
        $this->another_group = $anothe_group;

        if (!$this->checkParams($objectType, $objectId, $scheduleType, $scheduleStartDate, $scheduleEndDate)) {
            return false;
        }
        $this->userRef = $userRef;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->scheduleType = $scheduleType;
        if ($scheduleStartDate == $scheduleEndDate && $scheduleType == 'Week') {
            $weekdates = $this->getWeekDates($scheduleStartDate);
            $scheduleStartDate = $weekdates[0];
            $scheduleEndDate = $weekdates[1];
        }
        $this->scheduleStartDate = $this->parseDate($scheduleStartDate);
        $this->scheduleEndDate = $this->parseDate($scheduleEndDate);

        return true;
    }

    protected function parseDate($dateStr)
    {
        $date_arr = explode('.', $dateStr);
        if (sizeof($date_arr) >= 3) {
            return $date_arr[2] . $date_arr[1] . $date_arr[0];
        } else {
            return '';
        }
    }

    protected function GetSchedule(
        $objectType,
        $objectId,
        $scheduleType,
        $scheduleStartDate,
        $date_end,
        $recordbook = '',
        $employerRef = null
    )
    {
        $formatedData = null;

        $data = [
            'ScheduleObjectType' => $objectType,
            'ScheduleObjectId' => $objectId,
            'ScheduleType' => $scheduleType,
            'DateBegin' => date(self::DATA_TO_1C, strtotime($scheduleStartDate)),
            'DateEnd' => $date_end,
        ];
        if ($employerRef) {
            $data['EmployerRef'] = $employerRef;
        }
        $response = Yii::$app->soapClientStudent->load('GetSchedule', $data);

        $ownerRefName = null;
        if (
            isset($response) &&
            isset($response->return) &&
            isset($response->return->OwnerRef) &&
            isset($response->return->OwnerRef->ReferenceName)
        ) {
            $ownerRefName = $response->return->OwnerRef->ReferenceName;
        }

        if (isset($response->return->Week)) {
            $formatedData = $this->BuildScheduleFromXML(
                $response->return->Week,
                $recordbook,
                $objectType,
                $objectId,
                $ownerRefName
            );
        } elseif (isset($response->return->ProjectSchedule)) {
            if (!is_array($response->return->ProjectSchedule)) {
                $response->return->ProjectSchedule = [$response->return->ProjectSchedule];
            }
            $formatedData = $this->BuildScheduleFromXML(
                $response->return->ProjectSchedule,
                $recordbook,
                $objectType,
                $objectId,
                $ownerRefName
            );
        }

        
        if (
            $scheduleType == 'Full' &&
            !isset($response->return->Week) &&
            !isset($response->return->ProjectSchedule)
        ) {
            $formatedData = $this->BuildScheduleFromXML(
                $scheduleStartDate,
                $recordbook,
                $objectType,
                $objectId,
                $ownerRefName
            );
        }

        return $formatedData;
    }

    public function loadSchedule(?array $employerRef)
    {
        $formatedGroupData = [];
        $formatedData = [];

        if ($this->scheduleType == 'Full') {
            $date_end = date(self::DATA_TO_1C, strtotime($this->scheduleStartDate));
        } else {
            $date_end = date(self::DATA_TO_1C, strtotime($this->scheduleEndDate));
        }

        if (
            $this->objectId == '0' &&
            isset(Yii::$app->user->identity) &&
            Yii::$app->user->identity->isInRole(\common\models\User::ROLE_TEACHER)
        ) {
            $this->objectId = ArrayHelper::getValue(Yii::$app->user->identity, 'userRef.reference_id');
        }

        if (
            $this->objectType == 'AcademicGroup' &&
            isset(Yii::$app->user->identity) &&
            Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)
        ) {
            $formatedData_1 = [];
            $formatedData_2 = [];

            $formatedGroupData_1 = [];
            $formatedGroupData_2 = [];

            $recordbooks = $this->loadRecordBooks();

            $formatedGroupData_1 = $this->BuildGroupsFromXML($recordbooks);

            foreach ($recordbooks as $recordbook) {
                $formatedData_1[] = $this->GetSchedule(
                    $this->objectType,
                    $recordbook->AcademicGroupCompoundKey,
                    $this->scheduleType,
                    $this->scheduleStartDate,
                    $date_end,
                    $recordbook,
                    $employerRef
                );
            }

            $newGroup = false;
            if ($this->objectId != '0' && $this->another_group != '') {
                $newGroup = true;
                foreach ($recordbooks as $recordbook) {
                    if ($recordbook->AcademicGroupCompoundKey == $this->objectId) {
                        $newGroup = false;
                        break;
                    }
                }
            }

            if ($this->another_group != '' && $newGroup) {
                $formatedData_2[] = $this->GetSchedule($this->objectType, $this->objectId, $this->scheduleType, $this->scheduleStartDate, $date_end, '', $employerRef);

                $grp = [
                    'id' => '',
                    'name' => $this->another_group,
                    'speciality_name' => '',
                    'edu_form' => ''
                ];

                $formatedGroupData_2[] = $grp;
            }

            $formatedData = array_merge($formatedData_1, $formatedData_2);

            $formatedGroupData = array_merge($formatedGroupData_1, $formatedGroupData_2);
        } elseif ($this->objectId != '0' && $this->objectType != 'AcademicGroup') {
            $formatedData[] = $this->GetSchedule($this->objectType, $this->objectId, $this->scheduleType, $this->scheduleStartDate, $date_end, '', $employerRef);
        } elseif ($this->objectId != '0' && $this->objectType == 'AcademicGroup') {
            try {
                $formatedData[] = $this->GetSchedule($this->objectType, $this->objectId, $this->scheduleType, $this->scheduleStartDate, $date_end, '', $employerRef);
            } catch (Throwable $e) {
                $formatedData[] = $this->BuildScheduleFromXML($this->scheduleStartDate, '', $this->objectType, $this->objectId);
            }
        } else {
            $formatedData[] = $this->BuildScheduleFromXML($this->scheduleStartDate, '', $this->objectType, $this->objectId);
        }

        return ['data' => $formatedData, 'groups' => $formatedGroupData];
    }

    protected function checkParams($objectType, $objectId, $scheduleType, $scheduleStartDate, $scheduleEndDate)
    {
        if (
            EmptyCheck::isEmpty($objectId) ||
            EmptyCheck::isEmpty($objectType) ||
            EmptyCheck::isEmpty($scheduleType) ||
            EmptyCheck::isEmpty($scheduleEndDate) ||
            EmptyCheck::isEmpty($scheduleStartDate)
        ) {
            return false;
        }

        return true;
    }

    protected function getWeekDates($date)
    {
        $ts = strtotime($date);
        $start = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
        return array(
            date(self::DATA_TO_WEB, $start),
            date(self::DATA_TO_WEB, strtotime('next sunday', $start))
        );
    }


    protected function BuildGroupsFromXML($data)
    {
        $groupsXML = $data;
        $groups = [];

        foreach ($groupsXML as $groupXML) {
            $group = [
                'id' => $groupXML->CurriculumId,
                'name' => $groupXML->AcademicGroupName,
                'speciality_name' => $groupXML->SpecialtyName,
                'edu_form' => $groupXML->FormsOfEducationName,
            ];
            $groups[] = $group;
        }
        return $groups;
    }

    








    protected function BuildScheduleFromXML(
        $data,
        $recordbook,
        string $object_type,
        string $objectId,
        ?string $objectName = null
    ): ScheduleObject
    {
        $scheduleXMLs = $data;

        $scheduleObject = new ScheduleObject();
        if ($object_type == 'AcademicGroup' && $recordbook != '') {
            $scheduleObject->objectName = "{$recordbook->CurriculumName}, {$recordbook->AcademicGroupName}";
            $scheduleObject->objectType = $object_type;
            $scheduleObject->objectId = $recordbook->AcademicGroupCompoundKey;
        } else {
            $scheduleObject->objectName = $objectName;
            $scheduleObject->objectType = $object_type;
            $scheduleObject->objectId = $objectId;
        }

        if ($this->scheduleType == 'Week') {
            $scheduleObject->week = $this->buildWeek($scheduleXMLs);
        } else {
            $scheduleObject->project = [];
            if (is_string($scheduleXMLs)) {
                $scheduleObject->project[] = $this->buildProject($scheduleXMLs);
            } else {
                foreach ($scheduleXMLs as $xml) {
                    $scheduleObject->project[] = $this->buildProject($xml);
                }
            }
        }

        return $scheduleObject;
    }

    protected function buildLesson($xml_lesson)
    {
        if (!isset($xml_lesson->Lesson)) { 
            $lesson = new ScheduleLesson();
            $lesson->realTimeEnd = '';
            $lesson->realTimeStart = '';
            $lesson->timeEnd = date('H:i', strtotime($xml_lesson->DateEnd));
            $lesson->timeStart = date('H:i', strtotime($xml_lesson->DateBegin));

            $lesson->groups = [];
            $lesson->teachers = [];
            $lesson->classrooms = [];

            return $lesson;
        }

        $lesson_array = [];

        if (!is_array($xml_lesson->Lesson)) {
            $xml_lesson->Lesson = [$xml_lesson->Lesson];
        }

        foreach ($xml_lesson->Lesson as $Lesson) {
            $lesson = new ScheduleLesson();
            $lesson->realTimeEnd = '';
            $lesson->realTimeStart = '';
            $lesson->timeEnd = date('H:i', strtotime($xml_lesson->DateEnd));
            $lesson->timeStart = date('H:i', strtotime($xml_lesson->DateBegin));

            $realTimeEnd = date('H:i', strtotime($Lesson->DateEndReal));
            if ($realTimeEnd != $lesson->timeEnd) {
                $lesson->realTimeEnd = $realTimeEnd;
            }
            $realTimeStart = date('H:i', strtotime($Lesson->DateBeginReal));
            if ($realTimeStart != $lesson->timeStart) {
                $lesson->realTimeStart = $realTimeStart;
            }

            $lesson->groups = [];
            $lesson->teachers = [];
            $lesson->classrooms = [];

            $lesson->id = $Lesson->LessonCompoundKey;
            $lesson->lessonType = $Lesson->LessonType;
            $lesson->disciplineName = $Lesson->Subject;

            if (isset($Lesson->AcademicGroup)) {
                if (!is_array($Lesson->AcademicGroup)) {
                    $Lesson->AcademicGroup = [$Lesson->AcademicGroup];
                }

                foreach ($Lesson->AcademicGroup as $xml_group) {
                    $schedule_group = new ScheduleGroup();
                    $schedule_group->groupId = $xml_group->enc_value->AcademicGroupCompoundKey;
                    $schedule_group->groupName = $xml_group->enc_value->AcademicGroupName;

                    $lesson->groups[] = $schedule_group;
                }
            }

            if (isset($Lesson->Teacher)) {
                if (!is_array($Lesson->Teacher)) {
                    $Lesson->Teacher = [$Lesson->Teacher];
                }

                foreach ($Lesson->Teacher as $xml_teacher) {
                    $schedule_teacher = new ScheduleTeacher();
                    $schedule_teacher->teacherId = $xml_teacher->TeacherId;
                    $schedule_teacher->teacherName = $xml_teacher->TeacherName;

                    $lesson->teachers[] = $schedule_teacher;
                }
            }

            if (isset($Lesson->Classroom)) {
                if (!is_array($Lesson->Classroom)) {
                    $Lesson->Classroom = [$Lesson->Classroom];
                }

                foreach ($Lesson->Classroom as $xml_classroom) {
                    $schedule_classroom = new ScheduleClassroom();
                    $schedule_classroom->classroomId = $xml_classroom->ClassroomUID;
                    $schedule_classroom->classroomName = $xml_classroom->ClassroomName;
                    $schedule_classroom->classroomAddress = $xml_classroom->Address;

                    $lesson->classrooms[] = $schedule_classroom;
                }
            }

            $lesson_array[] = $lesson;
        }

        return $lesson_array;
    }

    protected function buildDay($xml_day)
    {
        $day = new ScheduleDay();
        if (is_object($xml_day)) {
            $day->date = date(self::DATA_TO_WEB, strtotime($xml_day->Date));
            $day->dayName = $xml_day->DayOfWeek;
            $lessons = [];

            if (isset($xml_day->ScheduleCell)) {
                if (!is_array($xml_day->ScheduleCell)) {
                    $xml_day->ScheduleCell = [$xml_day->ScheduleCell];
                }
                foreach ($xml_day->ScheduleCell as $xml_lesson) {
                    $result = $this->buildLesson($xml_lesson);
                    if (is_array($result)) {
                        $lessons = array_merge($lessons, $result);
                    } else {
                        $lessons[] = $result;
                    }
                }
            }
            ksort($lessons);
            $day->lessons = $lessons;
        } else {
            $day->date = date(self::DATA_TO_WEB, strtotime($xml_day));
            $day->dayName = '';
            $day->lessons = [];
        }
        return $day;
    }

    protected function buildProject($xml_project)
    {
        $project = new ScheduleProject();
        if (is_object($xml_project)) {
            $project->projectName = $xml_project->Project->ReferenceName;
            $projects = [];

            if (isset($xml_project->Day)) {
                if (!is_array($xml_project->Day)) {
                    $xml_project->Day = [$xml_project->Day];
                }
                foreach ($xml_project->Day as $xml_day) {
                    $result = $this->buildDay($xml_day);
                    if (is_array($result)) {
                        $projects = array_merge($projects, $result);
                    } else {
                        $projects[] = $result;
                    }
                }
            }
            $project->days = $projects;
        } else {
            $project->projectName = '';
            $project->days = [];
        }
        return $project;
    }

    protected function buildWeek($xml_week)
    {
        $week = new ScheduleWeek();
        if (is_object($xml_week)) {
            $week->dateStart = date(self::DATA_TO_WEB, strtotime($xml_week->DateBegin));
            $week->dateEnd = date(self::DATA_TO_WEB, strtotime($xml_week->DateEnd));
            $project = [];
            if (isset($xml_week->ProjectSchedule)) {
                $projectSchedule = $xml_week->ProjectSchedule;
                if (!is_array($projectSchedule)) {
                    $projectSchedule = [$projectSchedule];
                }
                foreach ($projectSchedule as $xml_project) {
                    $project[] = $this->buildProject($xml_project);
                }
                $week->project = $project;
            } else {
                if (isset($xml_week->DateBegin)) {
                    $week->dateStart = date(self::DATA_TO_WEB, strtotime($xml_week->DateBegin));
                } else {
                    $week->dateStart = date(self::DATA_TO_WEB, strtotime($xml_week));
                }
                if (isset($xml_week->DateEnd)) {
                    $week->dateEnd = date(self::DATA_TO_WEB, strtotime($xml_week->DateEnd));
                } else {
                    $week->dateEnd = date(self::DATA_TO_WEB, strtotime($xml_week . '+6 days'));
                }
                $week->days = [];
            }
        }

        return $week;
    }

    


    private function loadRecordBooks(): array
    {
        return Yii::$app->getPortfolioService->loadRawRecordbooks(Yii::$app->user->identity->userRef->reference_id);
    }
}
