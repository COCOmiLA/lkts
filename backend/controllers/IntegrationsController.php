<?php

namespace backend\controllers;

use backend\models\IntegrationSetting;
use common\models\errors\RecordNotValid;
use common\services\messagesDeliver\sms\smsAero\SmsAeroSmsDeliverer;
use common\services\messagesDeliver\sms\smsc\SmscSmsDeliverer;
use common\services\messagesDeliver\sms\smsRu\SmsRuSmsDeliverer;
use common\services\messagesDeliver\sms\ssms\SsmsSmsDeliverer;
use common\services\messagesDeliver\telegram\TelegramMessageDeliverer;
use geoffry304\enveditor\components\EnvComponent;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;

class IntegrationsController extends \yii\web\Controller
{
    private EnvComponent $envEditor;

    public function __construct($id, $module, EnvComponent $envEditor, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->envEditor = $envEditor;
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['administrator']
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'smsDeliverers' => $this->getSmsSenders(),
            'integration_settings' => $this->getIntegrationSettings(),
            'telegram_settings' => $this->getTelegramConfig(),
        ]);
    }

    public function actionSaveSettings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $integration_settings = Yii::$app->request->post('integration_settings');
        try {
            foreach ($integration_settings as $name => $value) {
                $setting = IntegrationSetting::find()->where(['name' => $name])->one();
                if ($setting) {
                    $setting->value = $value;
                    if (!$setting->save()) {
                        throw new RecordNotValid($setting);
                    }
                }
            }
            $this->setEnvVariables(Yii::$app->request->post('environment_settings'));
            return ['status' => true];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function setEnvVariables(array $settings)
    {
        if ($settings) {
            $env = $this->envEditor->load(
                FileHelper::normalizePath(
                    Yii::getAlias('@base') . EnvSettingsController::ENV_FILE_PATH
                )
            );
            foreach ($settings as $key => $value) {
                if ($value) {
                    $env->setKey($key, $value);
                }
            }
            $env->save();
        }
    }

    private function getSmsSenders(): array
    {
        return [
            'SMS Aero' => [
                SmsAeroSmsDeliverer::SMSAERO_EMAIL_ENV => ['Email пользователя', getenv(SmsAeroSmsDeliverer::SMSAERO_EMAIL_ENV)],
                SmsAeroSmsDeliverer::SMSAERO_API_KEY_ENV => ['API key', getenv(SmsAeroSmsDeliverer::SMSAERO_API_KEY_ENV)],
            ],
            'Smsc.ru' => [
                SmscSmsDeliverer::SMSC_LOGIN_ENV => ['Логин', getenv(SmscSmsDeliverer::SMSC_LOGIN_ENV)],
                SmscSmsDeliverer::SMSC_PASSWORD_ENV => ['Пароль', getenv(SmscSmsDeliverer::SMSC_PASSWORD_ENV)],
            ],
            'SMS.RU' => [
                SmsRuSmsDeliverer::SMSRU_API_KEY_ENV => ['API key', getenv(SmsRuSmsDeliverer::SMSRU_API_KEY_ENV)],
            ],
            'Ssms.su' => [
                SsmsSmsDeliverer::SMSS_API_KEY_ENV => ['API key', getenv(SsmsSmsDeliverer::SMSS_API_KEY_ENV)],
            ],
        ];
    }

    private function getTelegramConfig(): array
    {
        return [
            'Telegram Bot' => [
                TelegramMessageDeliverer::BOT_TOKEN_ENV => ['API Токен бота', getenv(TelegramMessageDeliverer::BOT_TOKEN_ENV)],
            ]
        ];
    }

    private function getIntegrationSettings(): array
    {
        $settings = IntegrationSetting::find()->all();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->name] = $setting->value;
        }
        return $result;
    }
}