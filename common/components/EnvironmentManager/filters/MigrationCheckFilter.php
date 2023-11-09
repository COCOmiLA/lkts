<?php

namespace common\components\EnvironmentManager\filters;

use common\components\EnvironmentManager\EnvironmentManager;
use common\components\EnvironmentManager\exceptions\MigrationsNotAppliedException;
use yii\base\Action;

class MigrationCheckFilter extends \yii\base\ActionFilter
{
    




    public function beforeAction($action) {
        if(EnvironmentManager::NeedToCheckMigrations()) {
            
            EnvironmentManager::EnsureMigrationsApplied();
        }
        return parent::beforeAction($action);
    }
}
