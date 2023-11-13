<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210115_151720_add_child_discipline_id_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'child_discipline_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-bachelor_egeresult-child_discipline_id}}',
            '{{%bachelor_egeresult}}',
            'child_discipline_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-child_discipline_id}}',
            '{{%bachelor_egeresult}}',
            'child_discipline_id',
            '{{%dictionary_ege_discipline}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-child_discipline_id}}',
            '{{%bachelor_egeresult}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_egeresult-child_discipline_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropColumn('{{%bachelor_egeresult}}', 'child_discipline_id');
    }
}
