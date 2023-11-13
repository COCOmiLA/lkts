<?php

namespace common\modules\student\components\orders;


use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class OrdersWidget extends Widget
{


    public $list;

    public function init()
    {
        parent::init();

        $user = Yii::$app->user->identity;


        $orderLoader = Yii::$app->getModule('student')->ordersLoader;
        $orderLoader->setParams($user->guid);

        $this->list = $orderLoader->loadList();

    }

    public function run()
    {
        if (sizeof($this->list) > 0) {
            return $this->render('order_widget', ['list' => $this->list]);
        } else {
            return implode("\n", [
                    Html::beginTag('h4'),
                    'Нет данных',
                    Html::endTag('h4')
                ]) . "\n";
        }
    }

}

