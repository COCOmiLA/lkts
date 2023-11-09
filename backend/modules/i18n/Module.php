<?php

namespace backend\modules\i18n;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'backend\modules\i18n\controllers';

    


    public static function missingTranslation($event)
    {
        
    }
}
