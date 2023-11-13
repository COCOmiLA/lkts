<?php

namespace console\controllers;

use backend\components\KladrLoader;
use Yii;
use yii\console\Controller;




class AppController extends Controller
{
    public function actionUpdate()
    {
        $migration = new PortalMigrateController('migrate', Yii::$app);
        $migration->migrationTable = '{{%system_db_migration}}';
        $migration->runAction('up', ['migrationPath' => '@common/migrations/db/', 'interactive' => false]);
    }

    public function actionTestmail($to, $subject = null, $text = null)
    {
        Yii::$app->notifier->sendMail($to, $subject == null ? 'test mail' : $text, $text == null ? 'test mail' : $text);
    }

    public function actionLists()
    {
        Yii::$app->admissionParser->loadFiles();
    }

    public function actionKladr(string $mode = 'file')
    {
        $errors = KladrLoader::loadKladr($mode);
        if (!$errors) {
            echo 'Справочник "КЛАДР" установлен успешно' . PHP_EOL;
        } else {
            echo 'Ошибка установки справочника "КЛАДР"' . PHP_EOL;
        }
    }
}
