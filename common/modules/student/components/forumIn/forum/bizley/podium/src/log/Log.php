<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\log;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\web\Application;







class Log
{
    



    public static function blame()
    {
        if (Yii::$app instanceof Application && !Podium::getInstance()->user->isGuest) {
            return User::loggedId();
        }
        return null;
    }

    



    public static function getTypes()
    {
        return [
            1 => Yii::t('podium/view', 'error'),
            2 => Yii::t('podium/view', 'warning'),
            4 => Yii::t('podium/view', 'info')
        ];
    }

    





    public static function error($msg, $model = null, $category = 'application')
    {
        Yii::error([
            'msg'   => $msg,
            'model' => $model,
        ], $category);
    }

    





    public static function info($msg, $model = null, $category = 'application')
    {
        Yii::info([
            'msg'   => $msg,
            'model' => $model,
        ], $category);
    }

    





    public static function warning($msg, $model = null, $category = 'application')
    {
        Yii::warning([
            'msg'   => $msg,
            'model' => $model,
        ], $category);
    }
}
