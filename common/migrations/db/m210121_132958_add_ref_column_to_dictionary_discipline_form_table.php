<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210121_132958_add_ref_column_to_dictionary_discipline_form_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_discipline_form}}', 'discipline_form_ref_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-dictionary_discipline_form-discipline_form_ref_id}}',
            '{{%dictionary_discipline_form}}',
            'discipline_form_ref_id'
        );

        
        $this->addForeignKey(
            '{{%fk-dictionary_discipline_form-discipline_form_ref_id}}',
            '{{%dictionary_discipline_form}}',
            'discipline_form_ref_id',
            '{{%discipline_form_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-dictionary_discipline_form-discipline_form_ref_id}}',
            '{{%dictionary_discipline_form}}'
        );

        
        $this->dropIndex(
            '{{%idx-dictionary_discipline_form-discipline_form_ref_id}}',
            '{{%dictionary_discipline_form}}'
        );
        $this->dropColumn('{{%dictionary_discipline_form}}', 'discipline_form_ref_id');
    }
}
