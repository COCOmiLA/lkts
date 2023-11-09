<?php

namespace console\controllers;


use console\traits\ChangeStdStreamsTrait;
use Yii;
use yii\base\UserException;

class PortalMigrateController extends \yii\console\controllers\MigrateController
{
    use ChangeStdStreamsTrait;

    protected function migrateUp($class)
    {
        ob_start();
        $result = parent::migrateUp($class);
        $message = ob_get_clean();

        if (!$result) {
            Yii::$app->supportInfo->print();
        }

        echo $message;

        \Yii::$app->db->schema->refresh();
        return $result;
    }

    protected function generateMigrationSourceCode($params)
    {
        if (key_exists($params['name'], $this->generatorTemplateFiles)) {
            $this->templateFile = $this->generatorTemplateFiles[$params['name']];
        }
        return parent::generateMigrationSourceCode($params);
    }

    public function beforeAction($action)
    {
        $parent = parent::beforeAction($action);
        if ($parent) {
            Yii::$app->releaseVersionProvider->clearVersionCache();
            if (!Yii::$app->releaseVersionProvider->isOneSServicesVersionMatches()) {
                throw new UserException(Yii::t(
                    'header/admin-interface',
                    'Предупреждение о том, что версия Информационной системы вуза не удовлетворяет минимальным требованиям к версии сервисов: `версия Информационной системы вуза не удовлетворяет минимальным требованиям Портала к версии сервисов.`',
                ));
            }
        }
        return $parent;
    }
}
