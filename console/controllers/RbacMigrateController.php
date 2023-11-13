<?php

namespace console\controllers;

use console\traits\ChangeStdStreamsTrait;
use yii\console\controllers\MigrateController;

class RbacMigrateController extends MigrateController
{
    use ChangeStdStreamsTrait;

    




    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);

        return new $class();
    }
}
