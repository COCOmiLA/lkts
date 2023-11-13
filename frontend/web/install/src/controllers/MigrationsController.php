<?php
$autoload = __DIR__ . '/../../../../../vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo "Не найден файл: " . $autoload;
    die();
}

require_once($autoload);

use backend\controllers\Migrate;
use common\components\ini\iniSet;
use yii\helpers\FileHelper;

class MigrationsController
{
    public function setup()
    {
        $root = __DIR__ . '/../../../../../';

        [
            'conf' => $conf,
            'path_pre' => $path_pre,
            'destination' => $destination,
        ] = $this->getPaths($root);

        require_once(__DIR__ . '/../components/MigrationsResult.php');
        require_once($path_pre);

        try {
            $result = $this->applyMigrations();
        } catch (\Throwable $e) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo $e->getMessage();
            die();
        }

        if (isset($result) && $result->complete === true) {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo $result->message;
            die();
        }

        if (isset($result) && $result->complete === false) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo $result->message;
            die();
        }

        http_response_code(400);
        header('Content-Type: text/plain');
        echo "Произошла ошибка";
        die();
    }

    


    protected function applyMigrations(): MigrationsResult
    {
        
        iniSet::disableTimeLimit();

        $message = (new Migrate(Migrate::TYPE_DB))->checkAction('up');
        $result = $this->processMigrationOutput($message);
        if ($result->complete) {
            $message = (new Migrate(Migrate::TYPE_RBAC))->checkAction('up');
            $result = $this->processMigrationOutput($message);
        }
        return $result;
    }

    protected function processMigrationOutput(string $message): MigrationsResult
    {
        if (strpos($message, 'Migrated up successfully.') !== false) {
            return new MigrationsResult(true, 'Все миграции применены');
        } elseif (strpos($message, 'No new migrations found. Your system is up-to-date.') !== false) {
            return new MigrationsResult(true, 'Все миграции применены. Нет новых миграций');
        } else {
            $message = 'Ошибка применения изменений к БД. <br />' . $message;
            \Yii::error(str_replace('<br />', '', $message));
            return new MigrationsResult(false, $message);
        }
    }

    private function getPaths($root)
    {
        $root = FileHelper::normalizePath($root);
        if ($root[-1] != DIRECTORY_SEPARATOR) {
            $root = $root . DIRECTORY_SEPARATOR;
        }
        $conf = 'confs' . DIRECTORY_SEPARATOR;
        $path_pre = $root . 'frontend/web/install/src/steps/pre.php';

        return [
            'conf' => $conf,
            'path_pre' => FileHelper::normalizePath($path_pre),
            'destination' => $root,
        ];
    }
}
