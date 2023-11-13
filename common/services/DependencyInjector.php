<?php

namespace common\services;

use backend\models\IntegrationSetting;
use common\components\BooleanCaster;
use common\services\messagesDeliver\sms\smsAero\SmsAeroSmsDeliverer;
use common\services\messagesDeliver\sms\smsc\SmscSmsDeliverer;
use common\services\messagesDeliver\sms\SmsDeliverer;
use common\services\messagesDeliver\sms\smsRu\SmsRuSmsDeliverer;
use common\services\messagesDeliver\sms\ssms\SsmsSmsDeliverer;
use common\services\messagesDeliver\telegram\TelegramMessageDeliverer;
use common\validators\CustomNumberValidator;
use ReflectionFunction;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;
use yii\web\ServerErrorHttpException;




class DependencyInjector implements BootstrapInterface
{
    public function bootstrap($app)
    {
        self::inject($app, Yii::$container);
    }

    


    public static function inject(Application $application, Container $container)
    {
        static::setComponentsToDI($application, $container);
        static::setSmsDeliverer($container);
        static::setTelegramBotDeliverer($container);
        static::setCustomValidators();

        $container->setSingleton(NamesManagementService::class);
    }

    





    private static function setComponentsToDI(Application $application, Container $container)
    {
        $components = $application->components;
        $names_to_skip = [
            'soapClientAbit',
            'soapClientStudent',
            'soapClientWebApplication',
            'urlManagerBackend',
            'urlManagerFrontend',
            'urlManagerStorage',
            'urlManager',
            'errorHandler',
            'user'
        ];

        foreach ($components as $name => $component) {
            if (in_array($name, $names_to_skip)) {
                continue;
            }
            $class = null;
            $already_initialized = $application->has($name, true);
            if (is_array($component)) {
                $class = $component['class'] ?? null;
            } else if (is_callable($component)) {
                
                $reflection = new ReflectionFunction($component);
                $return_type = $reflection->getReturnType();
                if ($return_type) {
                    $class = $return_type->getName();
                }
            } else if (is_object($component)) {
                $class = get_class($component);
            }
            if ($class) {
                if ($already_initialized) {
                    $container->setSingleton($class, $application->get($name));
                } else {
                    $container->setSingleton($class);
                }
            }
        }
    }

    private static function setSmsDeliverer(Container $container)
    {
        if (defined('PORTAL_CONSOLE_INSTALLATION')) {
            
            return;
        }
        
        if (Yii::$app->db->schema->getTableSchema(IntegrationSetting::tableName()) === null) {
            return;
        }
        $sms_sender = ArrayHelper::getValue(IntegrationSetting::find()->where(['name' => 'sms_sender'])->one(), 'value');
        if ($sms_sender) {
            if ($sms_sender == 'SMS Aero') {
                static::setSmsAeroDeliverer($container);
            } elseif ($sms_sender == 'Smsc.ru') {
                static::setSmscDeliverer($container);
            } elseif ($sms_sender == 'SMS.RU') {
                static::setSmsRuDeliverer($container);
            } elseif ($sms_sender == 'Ssms.su') {
                static::setSsmsDeliverer($container);
            } else {
                throw new ServerErrorHttpException('Неизвестный sms-рассыльщик');
            }
        }
    }

    private static function setSmscDeliverer(Container $container)
    {
        $container->set(SmsDeliverer::class, [
            'class' => SmscSmsDeliverer::class,
            'login' => getenv(SmscSmsDeliverer::SMSC_LOGIN_ENV),
            'password' => getenv(SmscSmsDeliverer::SMSC_PASSWORD_ENV),
            'use_https' => BooleanCaster::cast(getenv(SmscSmsDeliverer::SMSC_HTTPS_ENV)),
            'debug' => BooleanCaster::cast(getenv(SmscSmsDeliverer::SMSC_DEBUG_ENV)),
            'use_post' => true,
            'charset' => 'utf-8',
        ]);
    }

    private static function setSmsAeroDeliverer(Container $container)
    {
        $container->set(SmsDeliverer::class, [
            'class' => SmsAeroSmsDeliverer::class,
            'email' => getenv(SmsAeroSmsDeliverer::SMSAERO_EMAIL_ENV),
            'api_key' => getenv(SmsAeroSmsDeliverer::SMSAERO_API_KEY_ENV),
        ]);
    }

    private static function setSmsRuDeliverer(Container $container)
    {
        $container->set(SmsDeliverer::class, [
            'class' => SmsRuSmsDeliverer::class,
            'api_key' => getenv(SmsRuSmsDeliverer::SMSRU_API_KEY_ENV),
        ]);
    }

    private static function setSsmsDeliverer(Container $container)
    {
        $container->set(SmsDeliverer::class, [
            'class' => SsmsSmsDeliverer::class,
            'api_key' => getenv(SsmsSmsDeliverer::SMSS_API_KEY_ENV),
        ]);
    }

    private static function setTelegramBotDeliverer(Container $container)
    {
        $container->set(TelegramMessageDeliverer::class, [
            'bot_token' => getenv(TelegramMessageDeliverer::BOT_TOKEN_ENV),
        ]);
    }

    private static function setCustomValidators()
    {
        Validator::$builtInValidators['integer'] = [
            'integerOnly' => true,
            'class' => CustomNumberValidator::class,
        ];
    }
}
