<?php

namespace common\components\Migration;

use common\components\migrations\traits\TableOptionsTrait;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;
use common\models\EmptyCheck;
use Yii;
use yii\db\Migration;
use yii\db\MigrationInterface;
use yii\helpers\Console;

class MigrationWithDefaultOptions extends Migration implements MigrationInterface
{
    use TableOptionsTrait;
    use createDropReferenceTable;

    




    public function createTable($table, $columns, $options = null)
    {
        if (EmptyCheck::isEmpty($options)) {
            $options = self::GetTableOptions();
        }
        parent::createTable($table, $columns, $options);
    }

    










    public function addForeignKey(
        $name,
        $table,
        $columns,
        $refTable,
        $refColumns,
        $delete = null,
        $update = null
    ) {
        $tableSchema = $this->db->getTableSchema($table);
        $normalName = self::normalizeTablename($name);
        if (!array_key_exists($normalName, $tableSchema->foreignKeys)) {
            parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
        } else {
            $message = Console::ansiFormat(
                Yii::t(
                    'console',
                    "Внешний ключ - «{FK}» уже существует в таблице - «{TABLE}»",
                    [
                        'FK' => $name,
                        'TABLE' => self::normalizeTablename($table),
                    ]
                ),
                [Console::FG_GREEN]
            );
            echo $message . PHP_EOL;
        }
    }

    





    public function dropForeignKey($name, $table)
    {
        $tableSchema = $this->db->getTableSchema($table);
        $normalName = self::normalizeTablename($name);
        if (array_key_exists($normalName, $tableSchema->foreignKeys)) {
            parent::dropForeignKey($name, $table);
        } else {
            $message = Console::ansiFormat(
                Yii::t(
                    'console',
                    "Внешний ключ - «{FK}» не найден в таблице - «{TABLE}»",
                    [
                        'FK' => $name,
                        'TABLE' => self::normalizeTablename($table),
                    ]
                ),
                [Console::FG_YELLOW]
            );
            echo $message . PHP_EOL;
        }
    }
}
