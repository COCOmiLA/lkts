<?php

namespace common\behaviors;

use yii\base\Behavior;
use yii\web\User;




class LoginTimestampBehavior extends Behavior
{
    


    public $attribute = 'logged_at';


    


    public function events()
    {
        return [
            User::EVENT_AFTER_LOGIN => 'afterLogin'
        ];
    }

    


    public function afterLogin($event)
    {
        $user = $event->identity;
        $user->touch($this->attribute);
        $user->save(false);
    }
}
