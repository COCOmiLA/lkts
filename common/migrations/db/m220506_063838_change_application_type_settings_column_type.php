<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220506_063838_change_application_type_settings_column_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $column = 'value';
        $type = "{$this->integer()->defaultValue(0)}";
        if ($this->db->driverName === 'pgsql') {
            $type .= " USING ({$column}::{$this->integer()})";
        }
        $this->alterColumn('{{%application_type_settings}}', $column, $type);
    }

    


    public function safeDown()
    {
        $column = 'value';
        $type = $this->boolean();
        if ($this->db->driverName === 'pgsql') {
            $type->append("USING CASE WHEN {$column}=1 THEN TRUE ELSE FALSE END");
        }
        $this->alterColumn('{{%application_type_settings}}', $column, $type);
    }
}
