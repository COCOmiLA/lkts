<?php

namespace common\components\EnvironmentManager;

use common\components\EnvironmentManager\exceptions\EnvironmentException;
use common\components\EnvironmentManager\exceptions\MigrationsNotAppliedException;
use common\components\EnvironmentManager\exceptions\PHPEnvironmentException;
use common\components\EnvironmentManager\exceptions\UnsupportedDBMSException;
use Yii;

class EnvironmentManager
{
    const SUBVERSION_COUNT = 4;

    public static $portalDatabaseVersionTable = 'portal_database_version';

    


    private static function CheckPHP()
    {
        if (!preg_match('/7.4(.*)/', phpversion())) {
            throw new PHPEnvironmentException();
        }
    }

    


    private static function CheckDBMS()
    {
        
        if (!in_array(Yii::$app->db->driverName, ['mysql', 'pgsql'])) {
            throw new UnsupportedDBMSException();
        }
    }

    


    public static function CheckEnvironment()
    {
        if (self::NeedToCheckEnvironment()) {
            self::CheckPHP();
            self::CheckDBMS();
        }
    }

    public static function NeedToCheckEnvironment()
    {
        return getenv('ENABLE_ENVIRONMENT_CHECK') === 'true' || empty(getenv('ENABLE_ENVIRONMENT_CHECK'));
    }

    public static function NeedToCheckMigrations()
    {
        if (defined('PORTAL_CONSOLE_INSTALLATION')) {
            return false;
        }
        return getenv('ENABLE_MIGRATIONS_CHECK') === 'true' || empty(getenv('ENABLE_MIGRATIONS_CHECK'));
    }

    public static function GetDatabaseVersion()
    {
        return (new \yii\db\Query)
            ->select('version')
            ->from(static::$portalDatabaseVersionTable)
            ->orderBy(static::GetOrderByString())
            ->limit(1)
            ->scalar();
    }

    private static function GetOrderByString()
    {
        $initial = "updated_at DESC, ";

        for ($i = 0; $i < EnvironmentManager::SUBVERSION_COUNT; $i++) {
            $initial .= 'subversion' . ($i + 1) . ' DESC, ';
        }

        $initial .= 'version DESC';
        return $initial;
    }

    public static function GetMigrationsApplyingStatus()
    {
        $dbVersion = null;

        try {
            $dbVersion = self::GetDatabaseVersion();
        } catch (\yii\db\Exception $e) {
            Yii::error($e->getMessage());
        }
        return [$dbVersion == Yii::$app->version, empty($dbVersion) ? 'не определено' : $dbVersion, Yii::$app->version];
    }

    


    public static function EnsureMigrationsApplied()
    {
        [$state, $system_version, $portal_version] = EnvironmentManager::GetMigrationsApplyingStatus();
        if (!$state) {
            throw new MigrationsNotAppliedException(
                Yii::t("backend", "Обратитесь к администратору. Различаются версии базы данных ({$system_version}) и портала ({$portal_version})")
            );
        }
    }
}