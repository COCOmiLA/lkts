<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220425_111427_add_common_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%common_settings}}', [
            'id' => $this->primaryKey(),
            'show_technical_info_on_error' => $this->boolean()->defaultValue(false),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%common_settings}}');
    }
}
