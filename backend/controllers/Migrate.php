<?php







namespace backend\controllers;

use console\controllers\PortalMigrateController;
use console\controllers\RbacMigrateController;
use Yii;
use yii\base\UserException;

class Migrate
{
    const TYPE_DB = 'db';
    const TYPE_RBAC = 'rbac';

    protected $_migration_controller = null;
    protected array $_migration_options = [];

    public function __construct(string $type = Migrate::TYPE_DB)
    {
        switch ($type) {
            case Migrate::TYPE_DB:
                $this->_migration_controller = new PortalMigrateController('migrate', Yii::$app);
                $this->_migration_options = [
                    'migrationTable' => '{{%system_db_migration}}',
                    'migrationPath' => '@common/migrations/db/',
                ];
                break;
            case Migrate::TYPE_RBAC:
                $this->_migration_controller = new RbacMigrateController('rbac-migrate', Yii::$app);
                $this->_migration_options = [
                    'migrationTable' => '{{%system_rbac_migration}}',
                    'migrationPath' => '@common/migrations/rbac/',
                ];
                break;
            default:
                throw new UserException('unknown type: ' . $type);
        }
    }

    public function checkAction($action)
    {
        $out_file = tempnam(__DIR__ . '/../runtime', 'tmp_out');
        $out_stream = fopen($out_file, 'w');

        $err_file = tempnam(__DIR__ . '/../runtime', 'tmp_err');
        $err_stream = fopen($err_file, 'w');

        $error_output = '';
        ob_start();

        try {
            $this->_migration_controller->setStdOut($out_stream);
            $this->_migration_controller->setStdErr($err_stream);

            $this->_migration_controller->runAction(
                $action,
                array_merge(
                    $this->_migration_options,
                    [
                        'interactive' => false
                    ]
                )
            );

            $error_output = file_get_contents($err_file);
        } catch (\Throwable $e) {
            $error_output = $e->getMessage();
        }
        $verbose_output = ob_get_clean(); 

        $message = trim(file_get_contents($out_file) . "\n{$error_output}\n{$verbose_output}");
        $message = nl2br($message);

        fclose($out_stream);
        fclose($err_stream);

        unlink($out_file);
        unlink($err_file);

        return $message;
    }

    public function applyNewMigrate()
    {
        $message = $this->checkAction('up');

        if (strpos($message, 'Migrated up successfully.') !== false) {
            $message = 'Все изменения применены успешно';
        } else {
            $message = 'Ошибка применения изменений к БД. <br />' . $message;

            Yii::error(str_replace('<br />', '', $message));
        }

        return $message;
    }

    public function getNewMigrate()
    {
        
        
        return strpos($this->checkAction('new'), 'No new migrations found. Your system is up-to-date.') === false;
    }
}
