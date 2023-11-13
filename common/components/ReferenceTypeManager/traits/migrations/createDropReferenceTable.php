<?php

namespace common\components\ReferenceTypeManager\traits\migrations;

use common\components\Migration\MigrationWithDefaultOptions;

trait createDropReferenceTable
{
    public function normalizeFormattedName(string $tableName): string
    {
        $pattern = '/(\{\{\%)([\w-]+)(\}\})/i';
        $replacement = '${2}';
        return preg_replace($pattern, $replacement, $tableName);
    }

    public function normalizeTablename(string $tableName): string
    {
        return $this->normalizeFormattedName($tableName);
    }

    private function getIndexName(string $tableName): string
    {
        return "idx_{$tableName}_archive";
    }

    function createReferenceTable($tableName, $tableOptions = null)
    {
        

        $tableName = $this->normalizeTablename($tableName);

        $this->createTable(
            "{{%{$tableName}}}",
            [
                'id' => $this->primaryKey(),

                'reference_name' => $this->string(1000)->null(),
                'reference_id' => $this->string(255)->null(),
                'reference_uid' => $this->string(255)->null(),
                'reference_class_name' => $this->string(1000)->null(),

                'reference_data_version' => $this->string(),
                'reference_parent_uid' => $this->string(),
                'is_folder' => $this->boolean()->defaultValue(false),
                'has_deletion_mark' => $this->boolean()->defaultValue(false),
                'posted' => $this->boolean()->defaultValue(false),
                'is_predefined' => $this->boolean()->defaultValue(false),
                'predefined_data_name' => $this->string(1000),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
            ]
        );

        $this->createIndex($this->getIndexName($tableName), $tableName, 'archive');
    }

    function dropReferenceTable($tableName)
    {
        

        $tableName = $this->normalizeTablename($tableName);

        $this->dropIndex($this->getIndexName($tableName), "{{%{$tableName}}}");

        $this->dropTable("{{%{$tableName}}}");
    }
}
