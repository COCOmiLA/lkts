<?php

namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Controller;





class GlobalAccessBehavior extends Behavior
{

    



    public $rules = [];

    


    public $accessControlFilter = \yii\filters\AccessControl::class;

    












    public $denyCallback;

    


    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    public function beforeAction()
    {
        Yii::$app->controller->attachBehavior('global-access', [
            'class' => $this->accessControlFilter,
            'denyCallback' => $this->denyCallback,
            'rules'=> $this->rules
        ]);
    }
}
