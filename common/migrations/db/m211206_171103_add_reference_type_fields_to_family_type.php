<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211206_171103_add_reference_type_fields_to_family_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_family_type}}', 'data_version', $this->string());
        $this->addColumn('{{%dictionary_family_type}}', 'parent_key', $this->string());
        $this->addColumn('{{%dictionary_family_type}}', 'is_folder', $this->boolean());
        $this->addColumn('{{%dictionary_family_type}}', 'has_deletion_mark', $this->boolean());
        $this->addColumn('{{%dictionary_family_type}}', 'posted', $this->boolean());
        $this->addColumn('{{%dictionary_family_type}}', 'is_predefined', $this->boolean());
        $this->addColumn('{{%dictionary_family_type}}', 'predefined_data_name', $this->string(1000));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_family_type}}', 'data_version');
        $this->dropColumn('{{%dictionary_family_type}}', 'parent_key');
        $this->dropColumn('{{%dictionary_family_type}}', 'is_folder');
        $this->dropColumn('{{%dictionary_family_type}}', 'has_deletion_mark');
        $this->dropColumn('{{%dictionary_family_type}}', 'posted');
        $this->dropColumn('{{%dictionary_family_type}}', 'is_predefined');
        $this->dropColumn('{{%dictionary_family_type}}', 'predefined_data_name');
    }
}
