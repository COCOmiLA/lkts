<?php

namespace common\modules\student\components\graduateWork;

use common\models\User;
use common\modules\student\components\graduateWork\models\Theme;
use common\modules\student\models\RecordBook;
use common\modules\student\models\ReferenceType;
use Yii;
use yii\base\Component;

class GraduateWorkLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $login;
    public $password;

    protected $client;

    public $componentName = "Информация о темах курсовых и дипломных работ";
    public $baseRoute = 'student/graduateWork';

    public $guid;

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
            'class' => \common\modules\student\components\graduateWork\GraduateWorkLoader::class,
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\GraduateworkController';
    }

    public static function getUrlRules()
    {
        return [
            'student/graduateWork' => 'graduatework/index',
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

    public function loadRecordBooks()
    {
        if ($this->checkParams($this->guid)) {
            if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(\common\models\User::ROLE_STUDENT)) {
                $recordbooks = Yii::$app->getPortfolioService->loadRawRecordbooks(Yii::$app->user->identity->userRef->reference_id);

                return $this->BuildRecordBooksFromXML($recordbooks);
            }
        }

        return null;
    }

    public function loadCourseGraduateWorks($record_book_id)
    {
        if ($this->checkParams($this->guid) && $record_book_id != null) {
            $formattedData = [];

            $response = Yii::$app->soapClientStudent->load(
                "GetCourseGraduateWorks",
                [
                    'UserId' => $this->guid,
                    'RecordbookId' => $record_book_id,
                ]
            );

            if ($response === false) {
                return null;
            }

            $formattedData = $this->BuildCourseGraduateWorksFromXML($response->return);

            return $formattedData;
        } else {
            return null;
        }
    }

    public static function BuildRecordBooksFromXML($data)
    {
        $plans = [];
        $xml_plans = $data;
        if (is_array($xml_plans)) {
            foreach ($xml_plans as $xml_plan) {
                $plan = new RecordBook();
                $plan->id = $xml_plan->RecordbookId;
                $plan->curriculumId = $xml_plan->CurriculumId;
                if (strpos($plan->name, 'Зачетная книжка №') === false) {
                    $plan->name = "Зачетная книжка №{$xml_plan->RecordbookName}. {$xml_plan->CurriculumName}";
                }
                $plans[] = $plan;
            }
        } else {
            $xml_plan = $xml_plans;
            $plan = new RecordBook();
            $plan->id = $xml_plan->RecordbookId;
            $plan->curriculumId = $xml_plan->CurriculumId;
            if (strpos($plan->name, 'Зачетная книжка №') === false) {
                $plan->name = "Зачетная книжка №{$xml_plan->RecordbookName}. {$xml_plan->CurriculumName}";
            }
            $plans[] = $plan;
        }
        return $plans;
    }

    protected function BuildCourseGraduateWorksFromXML($data)
    {
        if (!isset($data->ThemeRecord)) {
            return [];
        }

        $theme_records = [];
        $xml_course_graduate_works = is_array($data->ThemeRecord) ? $data->ThemeRecord : $data;

        foreach ($xml_course_graduate_works as $xml_course_graduate_work) {
            $theme_record = new Theme();

            $theme_record->subjectRef = ReferenceType::BuildRefFromXML($xml_course_graduate_work->Subject);
            $theme_record->termRef = ReferenceType::BuildRefFromXML($xml_course_graduate_work->Term);
            $theme_record->theme = $xml_course_graduate_work->Theme;
            $theme_record->typeOfTheControlRef = ReferenceType::BuildRefFromXML($xml_course_graduate_work->TypeOfTheControl);
            $theme_record->teacherRef = ReferenceType::BuildRefFromXML($xml_course_graduate_work->Teacher);
            $theme_record->orderDate = $xml_course_graduate_work->OrderDate;
            $theme_record->orderNumber = $xml_course_graduate_work->OrderNumber;
            $theme_record->startDate = $xml_course_graduate_work->StartDate;
            $theme_record->orderRef = ReferenceType::BuildRefFromXML($xml_course_graduate_work->OrderRef);

            $theme_records[] = $theme_record;
        }

        return $theme_records;
    }
}
