<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230428_112929_create_gin_IDX_for_postgreSQL extends MigrationWithDefaultOptions
{
    private const TABLE_LIST = [
        ['table' => 'dictionary_fias',       'column' => 'name'],
        ['table' => 'dictionary_fias_doma',  'column' => 'name'],
        ['table' => 'dictionary_contractor', 'column' => 'name'],
    ];

    


    public function safeUp()
    {
        if (Yii::$app->db->driverName !== 'pgsql') {
            return true;
        }

        foreach (self::TABLE_LIST as ['table' => $table, 'column' => $column]) {
            $this->db->createCommand("
                CREATE INDEX gin_IDX_{$table}_{$column} ON
                \"{$table}\" USING GIN (to_tsvector('russian', \"{$column}\"))
            ")
                ->execute();
        }
    }

    


    public function safeDown()
    {
        if (Yii::$app->db->driverName !== 'pgsql') {
            return true;
        }

        foreach (self::TABLE_LIST as ['table' => $table, 'column' => $column]) {
            $this->db->createCommand("DROP INDEX gin_IDX_{$table}_{$column}")
                ->execute();
        }
    }
}
