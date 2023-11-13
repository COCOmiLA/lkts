<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200612_154625_add_archive_column_to_individual_achievements_document_types_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievements_document_types}}', 'archive', $this->boolean());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievements_document_types}}', 'archive');
    }
}
