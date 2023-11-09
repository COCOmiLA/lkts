<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200714_080353_add_from_1c_column_to_individual_achievements_document_types_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievements_document_types}}', 'from1c', $this->boolean()->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievements_document_types}}', 'from1c');
    }
}
