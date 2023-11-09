<?php

use common\components\Migration\MigrationWithDefaultOptions;
use \common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m221102_134130_create_contractor_reference_type_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;
    
    


    public function safeUp()
    {
        $this->createReferenceTable('contractor_reference_type');
        $this->createReferenceTable('contractor_type_reference_type');
        
        $this->createTable('{{%dictionary_contractor}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1000),
            'subdivision_code' => $this->string(),
            'contractor_ref_id' => $this->integer(),
            'contractor_type_ref_id' => $this->integer(),
            'status' => $this->string(),
            'archive' => $this->boolean()->defaultValue(false)
        ]);
        
        $this->createIndex(
            '{{%idx-dictionary_contractor-contractor_ref_id}}',
            '{{%dictionary_contractor}}', 
            'contractor_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_contractor-contractor_ref_id}}', 
            '{{%dictionary_contractor}}', 
            'contractor_ref_id', 
            '{{%contractor_reference_type}}', 
            'id'
        );
        
        $this->createIndex(
            '{{%idx-dictionary_contractor-contractor_type_ref_id}}',
            '{{%dictionary_contractor}}',
            'contractor_type_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-dictionary_contractor-contractor_type_ref_id}}', 
            '{{%dictionary_contractor}}', 
            'contractor_type_ref_id', 
            '{{%contractor_type_reference_type}}', 
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('{{%idx-dictionary_contractor-contractor_ref_id}}', '{{%dictionary_contractor}}');
        $this->dropIndex('{{%idx-dictionary_contractor-contractor_type_ref_id}}', '{{%dictionary_contractor}}');
        $this->dropForeignKey('fk-dictionary_contractor-contractor_ref_id', '{{%dictionary_contractor}}');
        $this->dropForeignKey('fk-dictionary_contractor-contractor_type_ref_id', '{{%dictionary_contractor}}');
        $this->dropTable('{{%dictionary_contractor}}');
        $this->dropTable('{{%contractor_type_reference_type}}');
        $this->dropTable('{{%contractor_reference_type}}');
    }
}
