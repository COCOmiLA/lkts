<?php

namespace common\components\EnvironmentManager\filters;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use yii\base\Action;

class CodeSettingsCheckFilter extends \yii\base\ActionFilter
{
    




    public function beforeAction($action)
    {
        if ($action->controller->module->id !== 'backend') {
            CodeSettingsManager::EnsureRequiredCodesAreFilled();
        }
        
        return parent::beforeAction($action);
    }
}
