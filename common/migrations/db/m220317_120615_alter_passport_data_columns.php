<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220317_120615_alter_passport_data_columns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%passport_data}}', 'issued_by', $this->string(1000)->null());
        $this->alterColumn('{{%passport_data}}', 'issued_date', $this->string(100)->null());
        $this->alterColumn('{{%passport_data}}', 'document_type_id', $this->integer(11)->null());
        $this->alterColumn('{{%passport_data}}', 'department_code', $this->string(50)->null());
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%passport_data}}', 'issued_by', $this->string(1000)->notNull());
        $this->alterColumn('{{%passport_data}}', 'issued_date', $this->string(100)->notNull());
        $this->alterColumn('{{%passport_data}}', 'document_type_id', $this->integer(11)->notNull());
        $this->alterColumn('{{%passport_data}}', 'department_code', $this->string(50)->notNull());
    }
}
