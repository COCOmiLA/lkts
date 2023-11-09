<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200520_082710_create_bachelor_preferences_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%bachelor_preferences}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
            'filename' => $this->string(255),
            'extension' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'olympiad_code' => $this->string(),
            'id_application' => $this->integer(),
            'privilege_code' => $this->string(),
            'special_mark_code' => $this->string(),
            'file' => $this->string(1000)->notNull(),
            'document_series' => $this->string(50)->notNull(),
            'document_number' => $this->string(50)->notNull(),
            'document_organization' => $this->string()->notNull(),
            'document_date' => $this->string()->notNull(),
            'document_type' => $this->string()->notNull(),
            'priority_right' => $this->boolean()->defaultValue(null),
            'individual_value' => $this->boolean()->defaultValue(null),
            'from1c' => $this->boolean()->defaultValue(false),
            'size' => $this->integer()->defaultValue(0),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%bachelor_preferences}}');
    }
}
