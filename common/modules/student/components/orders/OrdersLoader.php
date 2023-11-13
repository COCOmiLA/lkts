<?php

namespace common\modules\student\components\orders;

use common\models\EmptyCheck;
use common\models\User;
use common\modules\student\interfaces\DynamicComponentInterface;
use common\modules\student\interfaces\RoutableComponentInterface;
use Yii;
use yii\base\Component;

class OrdersLoader extends Component implements DynamicComponentInterface, RoutableComponentInterface
{
    public $userId;

    public $login;
    public $password;

    public $serviceUrl;

    protected $client;

    public $componentName = "Список приказов";
    public $baseRoute = 'student/orders';

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
            'class' => 'common\modules\student\components\orders\OrdersLoader',
            'serviceUrl' => getenv('SERVICE_URI') . 'Students/Orders/',
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\OrdersController';
    }

    public static function getUrlRules()
    {
        return ['student/orders' => 'orders/index'];
    }


    public function setParams($userId)
    {
        if ($this->checkParams($userId)) {
            $this->userId = $userId;
            return true;
        } else {
            return false;
        }
    }

    public function loadList()
    {
        $responseData = [];

        $recordbooks = $this->loadRecordBooks();

        foreach ($recordbooks as $recordbook) {
            $response = Yii::$app->soapClientStudent->load(
                'GetOrders',
                [
                    'UserId' => $this->userId,
                    'RecordbookId' => $recordbook->RecordbookId
                ]
            );

            if ($response === false) {
                continue;
            }

            $data = [];

            if (isset($response->return->OrdersInfo) && $response->return->OrdersInfo != null) {
                foreach ($response->return->OrdersInfo as $order_info) {
                    if (isset($order_info->OrderInfo)) {
                        array_push($data, $order_info->OrderInfo);
                    } else {
                        array_push($data, $order_info);
                    }
                }

                $responseData[$recordbook->SpecialtyName] = $data;
            }
        }

        return $responseData;
    }

    protected function checkParams($userId)
    {
        return !EmptyCheck::isEmpty($userId);
    }

    protected function buildUrl()
    {
        $urlTemplate = "";
        if (substr($this->serviceUrl, -1) != '/') {
            $urlTemplate = $this->serviceUrl . '/';
        } else {
            $urlTemplate = $this->serviceUrl;
        }
        $url = $urlTemplate;

        return $url;
    }

    protected function BuildOrderFromXML($data)
    {
        $orders = [];

        $i = 0;
        foreach ($data as $gradeXML) {
            $order = new models\Order(
                (string)$gradeXML->Приказ
            );
            $orders[] = $order;
        }
        return $orders;
    }

    


    private function loadRecordBooks(): array
    {
        return Yii::$app->getPortfolioService->loadRawRecordbooks(Yii::$app->user->identity->userRef->reference_id);
    }
}
