<?php

use common\components\AuthManager;
use common\components\cache\PortalCacheFactory;
use common\components\ChecksumManager\ChecksumManager;
use common\components\ChecksumManager\models\Checksum;
use common\components\EnvironmentManager\EnvironmentManager;
use common\components\EnvironmentManager\filters\CodeSettingsCheckFilter;
use common\components\EnvironmentManager\filters\MigrationCheckFilter;
use common\components\EnvironmentManager\filters\VersionsCheckFilter;
use common\components\LocalizationManager;
use common\components\LogSettingsProvider\LogSettingsProvider;
use common\components\ReleaseVersionProvider;
use common\components\SecureHeaders\SecureHeaders;
use common\components\secureUrlManager\SecureUrlManager;
use common\components\SupportInfo\SupportInfo;
use common\models\logs\PortalDbTarget;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\services\DependencyInjector;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use yii\web\Session;
use yii2assets\pdfjs\Module;

$try_locales = [
    'en_US.UTF-8',
    'C.UTF-8'
];


setlocale(LC_ALL, $try_locales);

$config = [
    'name' => getenv('PORTAL_NAME'),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
    'language' => 'ru',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'bootstrap' => ['log', 'podium', 'headers', DependencyInjector::class],
    'modules' => [
        'podium' => ['class' => Podium::class],
        'pdfjs' => ['class' => Module::class],
    ],
    'components' => [
        'authManager' => [
            'class' => AuthManager::class,
            'itemTable' => '{{%rbac_auth_item}}',
            'itemChildTable' => '{{%rbac_auth_item_child}}',
            'assignmentTable' => '{{%rbac_auth_assignment}}',
            'ruleTable' => '{{%rbac_auth_rule}}'
        ],
        'session' => [
            'class' => Session::class,
            'name' => 'LKSESSID',
            'cookieParams' => [
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ]
        ],
        'cache' => function (): CacheInterface {
            $cache_factory = new PortalCacheFactory();
            return $cache_factory->createCache();
        },

        'commandBus' => [
            'class' => \trntv\tactician\Tactician::class,
            'commandNameExtractor' => '\League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor',
            'methodNameInflector' => '\League\Tactician\Handler\MethodNameInflector\HandleInflector',
            'commandToHandlerMap' => [
                'common\commands\command\SendEmailCommand' => '\common\commands\handler\SendEmailHandler',
                'common\commands\command\AddToTimelineCommand' => '\common\commands\handler\AddToTimelineHandler',
            ]
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter'
        ],
        'glide' => [
            'class' => \trntv\glide\components\Glide::class,
            'sourcePath' => '@storage/web/source',
            'cachePath' => '@storage/cache',
            'urlManager' => 'urlManagerStorage',
            'maxImageSize' => '4000000',
            'signKey' => 'g4DopBf81M6vOi45bJalkXTumDg0rwph'
        ],
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
            'htmlLayout' => '@common/mail/layouts/html',
            'textLayout' => '@common/mail/layouts/text',
            'useFileTransport' => false,
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => [getenv('FROM_EMAIL') => getenv('PORTAL_NAME')]
            ],
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('MAIL_HOST'),
                'username' => getenv('MAIL_USERNAME'),
                'password' => getenv('MAIL_PASSWORD'),
                'port' => getenv('MAIL_PORT'),
                'encryption' => strlen(getenv('MAIL_PROTOCOL')) > 0 ? getenv('MAIL_PROTOCOL') : null,
            ],
        ],
        'db' => [
            'class' => Connection::class,
            'dsn' => getenv('DB_DSN'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'tablePrefix' => getenv('DB_TABLE_PREFIX'),
            'charset' => 'utf8',
            'enableSchemaCache' => YII_ENV_PROD,
            'schemaMap' => [
                'pgsql' => [
                    'class' => \yii\db\pgsql\Schema::class,
                    'defaultSchema' => 'public'
                ],
                'mysql' => [
                    'class' => \yii\db\mysql\Schema::class,
                ]
            ],
        ],
        'log' => [
            'traceLevel' => 3,
            'flushInterval' => 1,
            'targets' => [
                'db_info' => [
                    'class' => PortalDbTarget::class,
                    'levels' => ['info'],
                    'categories' => ['info'],
                    'except' => ['yii\debug\Module*'],
                    'prefix' => function () {
                        return LogSettingsProvider::infoLogPrefix();
                    },
                    'logVars' => [],
                    'logTable' => '{{%system_log_info}}',
                    'maskVars' => \common\models\logs\LogVarsExcluder::excludeVars()
                ],
                'db' => [
                    'class' => PortalDbTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:*', 'yii\i18n\I18N\*', 'yii\debug\Module*'],
                    'prefix' => function () {
                        return LogSettingsProvider::systemLogPrefixForDb();
                    },
                    'logVars' => [],
                    'logTable' => '{{%system_log}}',
                    'maskVars' => \common\models\logs\LogVarsExcluder::excludeVars()
                ],
                'file_info' => [
                    'class' => FileTarget::class,
                    'levels' => ['info'],
                    'categories' => ['info'],
                    'except' => ['yii\debug\Module*'],
                    'prefix' => function () {
                        return LogSettingsProvider::infoLogPrefix();
                    },
                    'logVars' => [],
                    'logFile' => '@common/runtime/logs/yii_info.log',
                    'maxFileSize' => 1024, 
                    'maxLogFiles' => 10000,
                    'exportInterval' => 10,
                    'maskVars' => \common\models\logs\LogVarsExcluder::excludeVars()
                ],
                'file' => [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:*', 'yii\i18n\I18N\*', 'yii\debug\Module*'],
                    'prefix' => function () {
                        return LogSettingsProvider::systemLogPrefixForFile();
                    },
                    'logFile' => '@common/runtime/logs/yii.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'exportInterval' => 10,
                    'maskVars' => \common\models\logs\LogVarsExcluder::excludeVars()
                ],
            ],
        ],
        'keyStorage' => [
            'class' => 'common\components\keyStorage\KeyStorage'
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@common/messages',
                ],
                '*' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'common' => 'common.php',
                        'backend' => 'backend.php',
                        'frontend' => 'frontend.php',
                    ],
                    'on missingTranslation' => ['\backend\modules\i18n\Module', 'missingTranslation']
                ],
            ],
        ],

        'soapClientAbit' => [
            'class' => 'common\components\soapClientManager',
            'wsdl' => getenv("ABIT_WSDL"),
            'login' => getenv("ABIT_LOGIN"),
            'password' => getenv("ABIT_PASSWORD"),
            'debug' => 1
        ],
        'soapClientStudent' => [
            'class' => 'common\components\soapClientManager',
            'wsdl' => getenv("STUDENT_WSDL"),
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
            'debug' => 1
        ],
        'soapClientWebApplication' => [
            'class' => 'common\components\soapClientManager',
            'wsdl' => getenv("WEB_APP_WSDL"),
            'login' => getenv("WEB_APP_LOGIN"),
            'password' => getenv("WEB_APP_PASSWORD"),
            'debug' => 1
        ],
        'authentication1CManager' => [
            'class' => 'common\components\authentication1CManager',
        ],
        'dictionaryManager' => [
            'class' => 'common\components\dictionaryManager\dictionaryManager',
        ],
        'configurationManager' => [
            'class' => 'common\components\configurationManager'
        ],
        'notifier' => [
            'class' => 'common\components\notifier\notifier',
        ],
        'urlManagerBackend' => ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@backendUrl')
            ],
            require(Yii::getAlias('@backend/config/_urlManager.php'))
        ),
        'urlManagerFrontend' => ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@frontendUrl')
            ],
            require(Yii::getAlias('@frontend/config/_urlManager.php'))
        ),
        'urlManagerStorage' => ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@storageUrl')
            ],
            require(Yii::getAlias('@storage/config/_urlManager.php'))
        ),
        'admissionParser' => [
            'class' => 'common\components\admissionParser',
        ],
        'treeLoader' => [
            'class' => 'common\components\tree\TreeLoader',
        ],
        'getPortfolioService' => [
            'class' => 'common\components\getPortfolioService',
        ],
        'localizationManager' => [
            'class' => LocalizationManager::class,
        ],
        'headers' => [
            'class' => SecureHeaders::class,
            'upgradeInsecureRequests' => SecureUrlManager::isHttpsEnabled(),
            'blockAllMixedContent' => true,
            'requireSriForScript' => false,
            'requireSriForStyle' => false,
            'xssProtection' => true,
            'contentTypeOptions' => true,
            'strictTransportSecurity' => [
                'max-age' => 10,
                'includeSubDomains' => true,
                'preload' => false
            ],
            'xFrameOptions' => 'SAMEORIGIN', 
            'xPoweredBy' => 'Infocom-S',
            'referrerPolicy' => 'strict-origin-when-cross-origin',
            'cspDirectives' => require(__DIR__ . '/csp_config.php'),
        ],
        'releaseVersionProvider' => [
            'class' => ReleaseVersionProvider::class,
        ],
        'supportInfo' => [
            'class' => SupportInfo::class,
        ]
    ],
    'params' => [
        'bsVersion' => '4.x',
        'icon-framework' => \kartik\icons\Icon::FA,  
        'adminEmail' => getenv('ADMIN_EMAIL'),
        'robotEmail' => [getenv('FROM_EMAIL') => getenv('PORTAL_NAME')],
        'minimal_1C_version' => '2.2.9.9',
        'availableLocales' => [
            
            'ru' => 'Русский (РФ)',
        ],
        Checksum::PARAM_VENDOR => '',
    ],
    'controllerMap' => [],
    'version' => '0.0.21.1',
    'on beforeRequest' => function () {
        EnvironmentManager::CheckEnvironment();
        ChecksumManager::initVendorChecksum();

        \Yii::$app->attachBehavior('migrationCheck', [
            'class' => MigrationCheckFilter::class,
            'except' => [
                'site/error',
                'site/index',
                'migrate/*',
                'rbac-migrate/*',
                'install-lk/index',
                'update/index',
                'update/update',
                '*/login',
                '*/logout',
                'timeline-event/index',
                'transfer/came-back'
            ],
        ]);

        \Yii::$app->attachBehavior('codeSettingsCheck', [
            'class' => CodeSettingsCheckFilter::class,
            'except' => [
                'site/error',
                'site/index',
                '*/login',
                '*/logout',
                'migrate/*',
                'rbac-migrate/*',
                'install-lk/index',
                'transfer/came-back'
            ],
        ]);
        \Yii::$app->attachBehavior('versionsCheck', [
            'class' => VersionsCheckFilter::class,
            'except' => [
                'site/error',
                'site/index',
                '*/login',
                '*/logout',
                'migrate/*',
                'rbac-migrate/*',
                'install-lk/index',
                'update/index',
                'update/update',
                'timeline-event/index',
                'transfer/came-back'
            ],
        ]);
    },
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module'
    ];
}

return $config;
