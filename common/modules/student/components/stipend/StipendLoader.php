<?php

namespace common\modules\student\components\stipend;

use common\models\User;
use common\modules\student\components\stipend\models\Stipend;
use common\modules\student\models\RecordBook;
use common\modules\student\models\ReferenceType;
use Yii;
use yii\base\Component;

class StipendLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $login;
    public $password;

    protected $client;

    public $componentName = "Стипендии и прочие выплаты";
    public $baseRoute = 'student/stipend';

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
            case(User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }

    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\stipend\StipendLoader',
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
            ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\StipendController';
    }

    public static function getUrlRules()
    {
        return [
            'student/stipend' => 'stipend/index',
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
                $recordbooks = Yii::$app->getPortfolioService->loadRawRecordbooks($this->guid);

                return $this->BuildRecordBooksFromXML($recordbooks);
            }
        }

        return null;
    }

    public function loadStudentStipends($record_book_id)
    {
        if ($this->checkParams($this->guid) && $record_book_id != null) {
            $formattedData = [];

            $response = Yii::$app->soapClientStudent->load("GetStudentStipends",
                [
                    'UserId' => $this->guid,
                    'RecordbookId' => $record_book_id,
                ]
            );

            if ($response === false) {
                return null;
            }

            $formattedData = StipendLoader::BuildStipendsFromXML($response->return);

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
                $plan->name = $xml_plan->CurriculumName;
                $plans[] = $plan;
            }
        } else {
            $xml_plan = $xml_plans;
            $plan = new RecordBook();
            $plan->id = $xml_plan->RecordbookId;
            $plan->curriculumId = $xml_plan->CurriculumId;
            $plan->name = $xml_plan->CurriculumName;
            $plans[] = $plan;
        }
        return $plans;
    }

    public static function BuildStipendsFromXML($data)
    {
        if (!isset($data->StipendRecord)) {
            return [];
        }

        $stipends = [];
        $xml_stipends = is_array($data->StipendRecord) ? $data->StipendRecord : $data;

        foreach ($xml_stipends as $xml_stipend) {
            $stipend = new Stipend();

            $stipend->orderNumber = $xml_stipend->OrderNumber;
            $stipend->orderDate = $xml_stipend->OrderDate;
            $stipend->orderRef = ReferenceType::BuildRefFromXML($xml_stipend->Order);
            $stipend->orderTypeRef = ReferenceType::BuildRefFromXML($xml_stipend->OrderType);
            $stipend->protocolNumber = $xml_stipend->ProtocolNumber;
            $stipend->protocolDate = $xml_stipend->ProtocolDate;
            $stipend->protocolRef = ReferenceType::BuildRefFromXML($xml_stipend->Protocol);
            $stipend->calculationRef = ReferenceType::BuildRefFromXML($xml_stipend->Calculation);
            $stipend->paymentAmount = $xml_stipend->PaymentAmount;
            $stipend->startDate = $xml_stipend->StartDate;
            $stipend->endDate = $xml_stipend->EndDate;
            $stipend->formOfEducationRef = ReferenceType::BuildRefFromXML($xml_stipend->FormOfEducation);
            $stipend->facultyRef = ReferenceType::BuildRefFromXML($xml_stipend->Faculty);
            $stipend->specialtyRef = ReferenceType::BuildRefFromXML($xml_stipend->Specialty);
            $stipend->courseRef = ReferenceType::BuildRefFromXML($xml_stipend->Course);
            $stipend->studyGroupRef = ReferenceType::BuildRefFromXML($xml_stipend->StudyGroup);
            $stipend->subgroupRef = ReferenceType::BuildRefFromXML($xml_stipend->Subgroup);
            $stipend->commissionDecisionRef = ReferenceType::BuildRefFromXML($xml_stipend->CommissionDecision);
            $stipend->causeRef = ReferenceType::BuildRefFromXML($xml_stipend->Cause);

            $stipends[] = $stipend;
        }

        return $stipends;
    }
}
