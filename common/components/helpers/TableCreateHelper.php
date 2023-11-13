<?php

namespace common\components\helpers;

use \yii\db\Connection;

class TableCreateHelper
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function createTempTable(string $tableName, array $columns): void
    {
        $collation_settings = '';
        if ($this->db->driverName === 'mysql') {
            $collation_settings = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci';
        }
        $this->db
            ->createCommand(
                "CREATE TEMPORARY TABLE IF NOT EXISTS {$this->db->quoteTableName($tableName)}
                 ( {$this->buildColumns($columns)} ) {$collation_settings};"
            )
            ->execute();
        $this->db->createCommand()->truncateTable($tableName)->execute();
    }

    private function buildColumns(array $columns): string
    {
        return implode(
            ', ',
            array_map(
                function (string $column_name, string $column_type) {
                    return "[[{$column_name}]] {$column_type}";
                },
                array_keys($columns),
                array_values($columns)
            )
        );
    }
}
