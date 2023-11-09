<?php

namespace common\components\migrations;

use common\components\EnvironmentManager\EnvironmentManager;
use common\components\Migration\MigrationWithDefaultOptions;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class VersionMigration extends MigrationWithDefaultOptions
{

    protected $version = '';

    public function insertRow($version)
    {
        $time = time();
        $exploded = explode('.', $version);

        if (count($exploded) !== EnvironmentManager::SUBVERSION_COUNT) {
            $count = EnvironmentManager::SUBVERSION_COUNT;
            throw new UserException("Количество подверсий должно быть равно {$count}, переданная версия: " . $version);
        }

        $subversions = [];

        for ($i = 0; $i < EnvironmentManager::SUBVERSION_COUNT; $i++) {
            $table = \Yii::$app->db->schema->getTableSchema('{{%portal_database_version}}');
            $column = 'subversion' . ($i + 1);
            if (isset($table->columns[$column])) {
                $subversions['subversion' . ($i + 1)] = $exploded[$i];
            }
        }

        $this->insert('{{%portal_database_version}}', ArrayHelper::merge([
            'version' => $version,
            'created_at' => $time,
            'updated_at' => $time,
        ], $subversions));
    }

    public function insertCurrent()
    {
        $this->insertRow($this->version);
    }

    public function up()
    {
        $this->insertCurrent();
        if (\Yii::$app && \Yii::$app->cache) {
            \Yii::$app->cache->flush();
        }
    }

    public function down()
    {
        return true;
    }
}
