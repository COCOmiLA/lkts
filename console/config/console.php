<?php

use common\components\SupportInfo\SupportInfoConsole;
use common\modules\student\Module;
use console\components\ErrorHandler;
use console\controllers\AddressController;
use console\controllers\DictionaryController;
use console\controllers\ExtendedMessageController;
use console\controllers\PortalMigrateController;
use console\controllers\RbacMigrateController;
use yii\base\ActionEvent;
use yii\base\InlineAction;

return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\controllers',
    'bootstrap' => ['student'],
    'controllerMap' => [
        'message' => ['class' => ExtendedMessageController::class],
        'migrate' => [
            'class' => PortalMigrateController::class,
            'migrationPath' => '@common/migrations/db',
            'migrationTable' => '{{%system_db_migration}}',
            'templateFile' => '@common/db/views/migration.php',
            'generatorTemplateFiles' => [
                'add_column' => '@common/db/views/addColumnMigration.php',
                'drop_table' => '@common/db/views/dropTableMigration.php',
                'drop_column' => '@common/db/views/dropColumnMigration.php',
                'create_table' => '@common/db/views/createTableMigration.php',
                'create_junction' => '@common/db/views/createTableMigration.php',
                'add_application_type_setting' => '@common/db/views/addApplicationTypeSetting.php',
                'refresh_application_type_setting' => '@common/db/views/refreshApplicationTypeSetting.php',
            ],
        ],
        'rbac-migrate' => [
            'class' => RbacMigrateController::class,
            'migrationPath' => '@common/migrations/rbac/',
            'migrationTable' => '{{%system_rbac_migration}}',
            'templateFile' => '@common/rbac/views/migration.php'
        ],
    ],
    'components' => [
        'urlManager' => ['baseUrl' => getenv('FRONTEND_URL')],
        'errorHandler' => ['class' => ErrorHandler::class],
        'supportInfo' => ['class' => SupportInfoConsole::class],
    ],
    'modules' => ['student' => ['class' => Module::class]],
    'on beforeAction' => function (ActionEvent $event) {
        if (
            $event->action instanceof InlineAction &&
            $event->action->actionMethod == 'actionUpdate' &&
            (
                $event->action->controller instanceof AddressController ||
                $event->action->controller instanceof DictionaryController
            )
        ) {
            define('PORTAL_CONSOLE_INSTALLATION', true);
        }
    }
];
