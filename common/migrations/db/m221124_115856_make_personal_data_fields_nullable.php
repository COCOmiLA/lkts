<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221124_115856_make_personal_data_fields_nullable extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%personal_data}}', 'firstname', $this->string()->null());
        $this->alterColumn('{{%personal_data}}', 'lastname', $this->string()->null());
        $this->alterColumn('{{%personal_data}}', 'birthdate', $this->string()->null());
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%personal_data}}', 'firstname', $this->string()->notNull());
        $this->alterColumn('{{%personal_data}}', 'lastname', $this->string()->notNull());
        $this->alterColumn('{{%personal_data}}', 'birthdate', $this->string()->notNull());
    }
}
