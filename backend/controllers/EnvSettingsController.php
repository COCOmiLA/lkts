<?php

namespace backend\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\Controller;





class EnvSettingsController extends Controller
{
    public const ENV_FILE_PATH = '/.env';
    private const EMPTY_ENV_FILE_PATH = '/confs/empty.env';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
        ];
    }

    


    public function actionIndex()
    {
        return $this->render(
            'index',
            ['missingEnvironmentSettings' => EnvSettingsController::getMissingEnvironmentSettings()]
        );
    }

    public function actionFillEnvVariables()
    {
        $diff = EnvSettingsController::getMissingEnvironmentSettings();
        $senValues = [];

        $envEditor = Yii::$app->env;
        $emptyEnv = $envEditor->load(FileHelper::normalizePath(
            Yii::getAlias('@base') . EnvSettingsController::EMPTY_ENV_FILE_PATH
        ));
        foreach ($diff as $key) {
            $senValues[$key] = $emptyEnv->getValue($key);
        }

        $env = $envEditor->load(FileHelper::normalizePath(
            Yii::getAlias('@base') . EnvSettingsController::ENV_FILE_PATH
        ));
        $env->addEmpty();
        foreach ($senValues as $key => $value) {
            $env->setKey($key, $value);
        }
        $env->save();

        return $this->redirect(['index']);
    }

    


    public static function getMissingEnvironmentSettings(): array
    {
        $envEditor = Yii::$app->env;
        $env = $envEditor->load(FileHelper::normalizePath(
            Yii::getAlias('@base') . EnvSettingsController::ENV_FILE_PATH
        ));
        $envKeys = array_keys($env->getKeys());

        $emptyEnv = $envEditor->load(FileHelper::normalizePath(
            Yii::getAlias('@base') . EnvSettingsController::EMPTY_ENV_FILE_PATH
        ));
        $emptyEnvKeys = array_keys($emptyEnv->getKeys());

        return array_diff($emptyEnvKeys, $envKeys);
    }

    


    public static function hasMissingEnvironmentSettings(): bool
    {
        return !empty(EnvSettingsController::getMissingEnvironmentSettings());
    }
}
