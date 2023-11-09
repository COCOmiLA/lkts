<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210408_142645_add_special_requirement_ref_id_to_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'special_requirement_ref_id', $this->integer());

        $this->createIndex(
            '{{%idx-bachelor_egeresult-special_requirement_ref_id}}',
            '{{%bachelor_egeresult}}',
            'special_requirement_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-special_requirement_ref_id}}',
            '{{%bachelor_egeresult}}',
            'special_requirement_ref_id',
            '{{%special_requirements}}',
            'id',
            'SET NULL'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-special_requirement_ref_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropIndex(
            '{{%idx-bachelor_egeresult-special_requirement_ref_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropColumn('{{%bachelor_egeresult}}', 'special_requirement_ref_id');
    }

}
