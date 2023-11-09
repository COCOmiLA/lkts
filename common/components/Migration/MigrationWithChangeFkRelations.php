<?php

namespace common\components\Migration;

use Exception;
use Throwable;
use Yii;
use yii\db\Query;
use yii\db\TableSchema;
use yii\helpers\Console;

class MigrationWithChangeFkRelations extends MigrationWithDefaultOptions
{
    public function changeAllForeignKeys($deleteAction = null, $allowNamePatternsForReftable = [])
    {
        $success = true;
        $dbSchema = Yii::$app->db->schema;
        $tables = $dbSchema->getTableSchemas();
        $tableCount = count($tables);
        foreach ($tables as $i => $table) {
            $success = $this->changeForeignKeyInTable($table, $deleteAction, $allowNamePatternsForReftable, $tableCount, $i);
            if (!$success) {
                return false;
            }
        }

        return $success;
    }

    public function changeForeignKeyInTable(TableSchema $table, $deleteAction = null, $allowNamePatternsForReftable = [], $tableCount = 0, $i = 0)
    {
        if (!$table->foreignKeys) {
            return true;
        }

        echo $this->printTransformationTableInfo($table->fullName, $tableCount, ++$i);

        $this_table_name = "{{%{$table->fullName}}}";
        
        
        
        $thisPrimaryKey = array_shift($table->primaryKey);

        $foreignKeys = $table->foreignKeys;
        foreach ($foreignKeys as $fkName => $fkStructure) {
            
            $that_table_name = "{{%{$fkStructure[0]}}}";
            unset($fkStructure[0]);

            if (
                $allowNamePatternsForReftable &&
                !$this->refTableIsAllowed($allowNamePatternsForReftable, $that_table_name)
            ) {
                continue;
            }

            foreach ($fkStructure as $thisColumnName => $thatColumnName) {
                if ($deleteAction == 'SET NULL' && !$table->columns[$thisColumnName]->allowNull) {
                    
                    
                    $this->setColumnAllowNull(
                        $this_table_name,
                        $thisColumnName,
                        $table->columns[$thisColumnName]->type,
                        $table->columns[$thisColumnName]->size
                    );
                }

                $this->changeForeignKeyRoutine(
                    $fkName,
                    $this_table_name,
                    $thisColumnName,
                    $thisPrimaryKey,
                    $that_table_name,
                    $thatColumnName,
                    $deleteAction
                );
            }
        }

        return true;
    }

    public function deleteUnRelatedRows(
        $this_table_name,
        $thisColumnName,
        $thisPrimaryKey,
        $that_table_name,
        $thatColumnName,
        $deleteAction = null
    ) {
        $leftJoinString = "{{%that_table_name}}.{$thatColumnName} = {$this_table_name}.{$thisColumnName}";
        if ($this->db->driverName === 'pgsql') {
            $leftJoinString = "{{%that_table_name}}.\"{$thatColumnName}\" = {$this_table_name}.\"{$thisColumnName}\"";
        }
        $unRelatedColumns = (new Query())
            ->select("{$this_table_name}.{$thisPrimaryKey}")
            ->from($this_table_name)
            ->leftJoin(['that_table_name' => $that_table_name], $leftJoinString)
            ->where([
                'and',
                ['IS', "{{%that_table_name}}.{$thatColumnName}", null],
                ['IS NOT', "{$this_table_name}.{$thisColumnName}", null],
            ])
            ->all();
        if (!$unRelatedColumns) {
            return true;
        }

        $deleteRowCount = count($unRelatedColumns);
        echo $this->printUnRelatedRowInfo($this_table_name, $that_table_name, $deleteRowCount);

        try {
            return $this->delete(
                $this_table_name,
                [$thisPrimaryKey => array_column($unRelatedColumns, $thisPrimaryKey)]
            );
        } catch (Throwable $th) {
            return $this->getTableFromErrorMessage($th->getMessage(), $deleteAction);
        }
    }

    public function getTableFromErrorMessage($error, $deleteAction = null)
    {
        $pattern = "/\(`\w+`.`(\w+)`,/";
        if (preg_match($pattern, $error, $matches)) {
            $dbSchema = Yii::$app->db->schema;
            $tables = $dbSchema->getTableSchema($matches[1], true);

            return $this->changeForeignKeyInTable($tables, $deleteAction);
        }

        throw new Exception("Не удалось выделить таблицу из: {$error}");
    }

    public function printTransformationTableInfo($tableFullName, $tableCount, $i)
    {
        $result = '';

        $result .= Console::ansiFormat(
            "\nКонвертирование 'Внешних ключей' для таблицы ",
            [Console::BG_BLACK, Console::FG_GREEN]
        );
        $result .= Console::ansiFormat(
            $tableFullName,
            [Console::BG_GREEN, Console::FG_BLACK]
        );
        if ($tableCount) {
            $result .= Console::ansiFormat(
                " ( {$i} / {$tableCount} )",
                [Console::BG_BLACK, Console::FG_GREEN]
            );
        }
        $result .= "\n";

        return $result;
    }

    public function printUnRelatedRowInfo($this_table_name, $that_table_name, $deleteRowCount)
    {
        $result = '';

        $result .= Console::ansiFormat(
            'Удаление битых связей из таблицы ',
            [Console::BG_BLACK, Console::FG_YELLOW]
        );
        $result .= Console::ansiFormat(
            $this_table_name,
            [Console::BG_YELLOW, Console::FG_BLACK]
        );
        $result .= Console::ansiFormat(
            ' к таблице ',
            [Console::BG_BLACK, Console::FG_YELLOW]
        );
        $result .= Console::ansiFormat(
            $that_table_name,
            [Console::BG_YELLOW, Console::FG_BLACK]
        );
        $result .= Console::ansiFormat(
            " ( {$deleteRowCount} )\n",
            [Console::BG_BLACK, Console::FG_YELLOW]
        );

        return $result;
    }

    public function setColumnAllowNull(
        $table,
        $name,
        $type,
        $size
    ) {
        $summaryType = $this->{$type}($size)->defaultValue(null);
        $this->alterColumn($table, $name, $summaryType);

        echo $this->printSetColumnAllowNull($table, $name);
    }

    public function printSetColumnAllowNull($table, $name)
    {
        $result = '';

        $result .= Console::ansiFormat(
            'Установка значение по умолчания как ',
            [Console::BG_BLACK, Console::FG_CYAN]
        );
        $result .= Console::ansiFormat(
            'NULL',
            [Console::BG_PURPLE, Console::FG_BLACK]
        );
        $result .= Console::ansiFormat(
            ' для колонки ',
            [Console::BG_BLACK, Console::FG_CYAN]
        );
        $result .= Console::ansiFormat(
            $name,
            [Console::BG_CYAN, Console::FG_BLACK]
        );
        $result .= Console::ansiFormat(
            ' в таблице ',
            [Console::BG_BLACK, Console::FG_CYAN]
        );
        $result .= Console::ansiFormat(
            $table,
            [Console::BG_CYAN, Console::FG_BLACK]
        );

        return $result;
    }

    public function changeForeignKeyRoutine(
        $fkName,
        $this_table_name,
        $thisColumnName,
        $thisPrimaryKey,
        $that_table_name,
        $thatColumnName,
        $deleteAction
    ) {
        $success = $this->deleteUnRelatedRows(
            $this_table_name,
            $thisColumnName,
            $thisPrimaryKey,
            $that_table_name,
            $thatColumnName,
        );
        if (!$success) {
            return;
        }

        $this->dropForeignKey($fkName, $this_table_name);

        Yii::$app->db->schema->refresh();

        try {
            $this->addForeignKey(
                $fkName,
                $this_table_name,
                $thisColumnName,
                $that_table_name,
                $thatColumnName,
                $deleteAction
            );
        } catch (Throwable $th) {
            echo Console::ansiFormat(
                "\n\nОшибка конвертирования 'Внешнего ключа': {$th->getMessage()}\n\n",
                [Console::BG_BLACK, Console::FG_RED]
            );

            $this->addForeignKey(
                $fkName,
                $this_table_name,
                $thisColumnName,
                $that_table_name,
                $thatColumnName
            );

            throw $th;
        }
    }

    public function refTableIsAllowed(
        $allowNamePatternsForReftable,
        $that_table_name
    ) {
        $isMatch = false;
        foreach ($allowNamePatternsForReftable as $pattern) {
            if (preg_match($pattern, $that_table_name)) {
                $isMatch = true;
                break;
            }
        }

        return $isMatch;
    }
}
