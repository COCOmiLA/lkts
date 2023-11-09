<?php

namespace common\components\Migration;

use Yii;
use yii\db\MigrationInterface;
use yii\db\TableSchema;
use yii\helpers\Console;

class SafeMigration extends MigrationWithDefaultOptions implements MigrationInterface
{
    




    public function createTable($table, $columns, $options = null)
    {
        if ($this->db->getTableSchema($table, true) !== null) {
            SafeMigration::renderSuccess(
                Yii::t('console', "Таблица «{TABLE}» уже существует"),
                ['TABLE' => SafeMigration::normalizeFormattedName($table)]
            );

            return;
        }

        parent::createTable($table, $columns, $options);
    }

    


    public function dropTable($table)
    {
        if ($this->getTableSchema($table) !== null) {
            parent::dropTable($table);
        }
    }

    




    public function addColumn($table, $column, $type)
    {
        if (($tableSchema = $this->getTableSchema($table)) === null) {
            return;
        }
        $column = SafeMigration::normalizeFormattedName($column);
        $columns = array_keys($tableSchema->columns);
        if (in_array($column, $columns)) {
            SafeMigration::renderSuccess(
                Yii::t('console', "Колонка - «{COLUMN}» уже существует в таблице - «{TABLE}»"),
                [
                    'COLUMN' => $column,
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );
            return;
        }

        parent::addColumn($table, $column, $type);
    }

    



    public function dropColumn($table, $column)
    {
        if (($tableSchema = $this->getTableSchema($table)) === null) {
            return;
        }
        $column = SafeMigration::normalizeFormattedName($column);
        $columns = array_keys($tableSchema->columns);
        if (!in_array($column, $columns)) {
            SafeMigration::renderWarning(
                Yii::t('console', "Колонка - «{COLUMN}» не найдена в таблице - «{TABLE}»"),
                [
                    'COLUMN' => $column,
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );
            return;
        }

        parent::dropColumn($table, $column);
    }

    





    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($this->getTableSchema($table) === null) {
            return;
        }
        if ($this->checkIfIndexExist($name, $table)) {
            SafeMigration::renderSuccess(
                Yii::t('console', "Индекс - «{IDX}» уже существует в таблице «{TABLE}»"),
                [
                    'IDX' => SafeMigration::normalizeFormattedName($name),
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );

            return;
        }

        parent::createIndex($name, $table, $columns, $unique);
    }

    



    public function dropIndex($name, $table)
    {
        if ($this->getTableSchema($table) === null) {
            return;
        }
        if (!$this->checkIfIndexExist($name, $table)) {
            SafeMigration::renderWarning(
                Yii::t('console', "Индекс - «{IDX}» не существует в таблице «{TABLE}»"),
                [
                    'IDX' => SafeMigration::normalizeFormattedName($name),
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );

            return;
        }

        parent::dropIndex($name, $table);
    }

    




    public function renameColumn($table, $name, $newName)
    {
        if (($tableSchema = $this->getTableSchema($table)) === null) {
            return;
        }

        $hasTrouble = false;
        $normalName = SafeMigration::normalizeFormattedName($name);
        $normalNewName = SafeMigration::normalizeFormattedName($newName);
        $columns = array_keys($tableSchema->columns);
        if (!in_array($normalName, $columns)) {
            SafeMigration::renderWarning(
                Yii::t('console', "Старое наименование колонки - «{OLD_COLUMN}» не найдено в таблице - «{TABLE}»"),
                [
                    'OLD_COLUMN' => $normalName,
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );

            $hasTrouble = true;
        }

        if (in_array($normalNewName, $columns)) {
            SafeMigration::renderSuccess(
                Yii::t('console', "Новое наименование колонки - «{NEW_COLUMN}» уже существует в таблице - «{TABLE}»"),
                [
                    'NEW_COLUMN' => $normalNewName,
                    'TABLE' => SafeMigration::normalizeFormattedName($table),
                ]
            );

            $hasTrouble = true;
        }

        if (!$hasTrouble) {
            parent::renameColumn($table, $name, $newName);
        }
    }

    




    public function getTableSchema(string $table): ?TableSchema
    {
        $tableSchema = $this->db->getTableSchema($table, true);
        if ($tableSchema === null) {
            SafeMigration::renderWarning(
                Yii::t('console', "Таблица «{TABLE}» не существует"),
                ['TABLE' => SafeMigration::normalizeFormattedName($table)]
            );
        }

        return $tableSchema;
    }

    



    private static function renderWarning(string $messageTemplate, array $params)
    {
        SafeMigration::renderMessage(
            Yii::t(
                'console',
                $messageTemplate,
                $params
            ),
            Console::FG_YELLOW
        );
    }

    



    private static function renderSuccess(string $messageTemplate, array $params)
    {
        SafeMigration::renderMessage(
            Yii::t(
                'console',
                $messageTemplate,
                $params
            ),
            Console::FG_GREEN
        );
    }

    



    private static function renderMessage(string $message, int $type)
    {
        $message = Console::ansiFormat($message, [$type]);
        echo $message . PHP_EOL;
    }

    





    private function checkIfIndexExist(string $name, string $table): bool
    {
        $connection = $this->db;
        $normalName = SafeMigration::normalizeFormattedName($name);
        $normalTable = SafeMigration::normalizeFormattedName($table);
        if ($connection->driverName === 'pgsql') {
            return (bool) $connection->createCommand("
                SELECT
                    COUNT(*)
                FROM
                    pg_stat_user_indexes
                WHERE
                    schemaname       = current_schema()
                    AND indexrelname = '$normalName'
                    AND relname      = '$normalTable'
            ")->queryScalar();
        }

        return (bool) $connection->createCommand("
            SELECT
                COUNT(*)
            FROM
                INFORMATION_SCHEMA.STATISTICS
            WHERE
                table_schema   = DATABASE()
                AND index_name = '$normalName'
                AND table_name = '$normalTable'
        ")->queryScalar();
    }
}
