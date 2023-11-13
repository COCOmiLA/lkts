<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220412_143118_add_columns_to_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'allow_add_new_file_after_app_approve', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%attachment_type}}', 'allow_delete_file_after_app_approve', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'allow_add_new_file_after_app_approve');
        $this->dropColumn('{{%attachment_type}}', 'allow_delete_file_after_app_approve');
    }
}
