<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220506_090330_alter_smallint_to_boolean extends MigrationWithDefaultOptions
{
    private const TABLES_TO_CONVERT = [
        ['{{%user}}', 'is_archive'],
        ['{{%address_data}}', 'homeless'],
        ['{{%address_data}}', 'not_found'],
        ['{{%admission_campaign}}', 'snils_allowed'],
        ['{{%admission_campaign}}', 'snils_is_required'],
        ['{{%application_type}}', 'blocked'],
        ['{{%attachment_type}}', 'required'],
        ['{{%attachment_type}}', 'from1c'],
        ['{{%attachment}}', 'deleted'],
        ['{{%attachment_type}}', 'is_using'],
        ['{{%attachment_type}}', 'hidden'],
        ['{{%bachelor_application}}', 'have_order'],
        ['{{%bachelor_application}}', 'need_exams'],
        ['{{%bachelor_egeresult}}', 'readonly'],
        ['{{%bachelor_speciality}}', 'readonly'],
        ['{{%dictionary_speciality}}', 'special_right'],
        ['{{%dictionary_allowed_forms}}', 'readonly'],
        ['{{%dictionary_speciality}}', 'receipt_allowed'],
        ['{{%education_data}}', 'have_original'],
        ['{{%exam_result}}', 'exam_register'],
        ['{{%exam_result}}', 'is_individual_achievement'],
        ['{{%personal_data}}', 'need_dormitory'],
        ['{{%personal_data}}', 'need_engineer_class'],
        ['{{%personal_data}}', 'need_pc_course'],
        ['{{%personal_data}}', 'need_po_course'],
    ];

    


    public function safeUp()
    {
        try {
            foreach (self::TABLES_TO_CONVERT as [$table, $column]) {

                if ($this->db->getTableSchema($table)->getColumn($column) === null || $this->db->getTableSchema($table)->getColumn($column)->type=='boolean') {
                    continue;
                }
                $type = $this->boolean()->defaultValue(false);
                if ($this->db->driverName === 'pgsql') {
                    $this->db->createCommand("alter table {$table} alter column {$column} drop default")->execute();
                    $type->append("USING CASE WHEN {$column}=1 THEN TRUE ELSE FALSE END");
                }
                $this->alterColumn($table, $column, $type);
            }
        } catch (Throwable $e) {
            $this->stdout($e->getMessage());
            return false;
        }
        return true;
    }

    


    public function safeDown()
    {
        foreach (self::TABLES_TO_CONVERT as [$table, $column]) {
            $type = "{$this->smallInteger()}";
            if ($this->db->driverName === 'pgsql') {
                $type .= " USING CASE WHEN {$column}=TRUE THEN 1 ELSE 0 END";
            }
            $this->alterColumn($table, $column, $type);
        }
    }
}
