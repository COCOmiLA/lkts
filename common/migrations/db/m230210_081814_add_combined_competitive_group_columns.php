<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230210_081814_add_combined_competitive_group_columns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'is_combined_competitive_group', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%dictionary_speciality}}', 'parent_combined_competitive_group_ref_id', $this->integer());

        
        $this->createIndex(
            '{{%idx-speciality-parent_combined_competitive_group_ref_id}}',
            '{{%dictionary_speciality}}',
            'parent_combined_competitive_group_ref_id'
        );
        
        $this->addForeignKey(
            '{{%fk-speciality-parent_combined_competitive_group_ref_id}}',
            '{{%dictionary_speciality}}',
            'parent_combined_competitive_group_ref_id',
            '{{%competitive_group_reference_type}}',
            'id'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-speciality-parent_combined_competitive_group_ref_id}}',
            '{{%dictionary_speciality}}'
        );
        
        $this->dropIndex(
            '{{%idx-speciality-parent_combined_competitive_group_ref_id}}',
            '{{%dictionary_speciality}}'
        );

        $this->dropColumn('{{%dictionary_speciality}}', 'is_combined_competitive_group');
        $this->dropColumn('{{%dictionary_speciality}}', 'parent_combined_competitive_group_ref_id');
    }
}
