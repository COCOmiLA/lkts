<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200621_231912_add_is_useing_filed_column_to_attachment_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'is_using', $this->boolean()->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'is_using');
    }
}
