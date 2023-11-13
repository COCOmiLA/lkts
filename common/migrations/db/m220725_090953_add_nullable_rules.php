<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220725_090953_add_nullable_rules extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%passport_data}}', 'issued_by', $this->string(1000)->null());
        $this->alterColumn('{{%passport_data}}', 'issued_date', $this->string(100)->null());
        $this->alterColumn('{{%passport_data}}', 'department_code', $this->string(50)->null());

        $this->alterColumn('{{%bachelor_target_reception}}', 'document_series', $this->string(50)->null());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_number', $this->string(50)->null());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_organization', $this->string(255)->null());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_date', $this->string(255)->null());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_type', $this->string(255)->null());

        $this->alterColumn('{{%bachelor_preferences}}', 'document_series', $this->string(50)->null());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_number', $this->string(50)->null());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_organization', $this->string(255)->null());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_date', $this->string(255)->null());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_type', $this->string(255)->null());
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%passport_data}}', 'issued_by', $this->string(1000)->notNull());
        $this->alterColumn('{{%passport_data}}', 'issued_date', $this->string(100)->notNull());
        $this->alterColumn('{{%passport_data}}', 'department_code', $this->string(50)->notNull());

        $this->alterColumn('{{%bachelor_target_reception}}', 'document_series', $this->string(50)->notNull());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_number', $this->string(50)->notNull());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_organization', $this->string(255)->notNull());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_date', $this->string(255)->notNull());
        $this->alterColumn('{{%bachelor_target_reception}}', 'document_type', $this->string(255)->notNull());

        $this->alterColumn('{{%bachelor_preferences}}', 'document_series', $this->string(50)->notNull());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_number', $this->string(50)->notNull());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_organization', $this->string(255)->notNull());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_date', $this->string(255)->notNull());
        $this->alterColumn('{{%bachelor_preferences}}', 'document_type', $this->string(255)->notNull());
    }
}
