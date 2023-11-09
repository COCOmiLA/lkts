<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211203_092357_add_parent_ref_id_to_parent_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%parent_data}}', 'parent_ref_id', $this->integer()->null());

        $this->addForeignKey(
            '{{%fk-parent_data-parent_ref_id}}',
            '{{%parent_data}}',
            'parent_ref_id',
            '{{%user_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-parent_data-parent_ref_id}}',
            '{{%parent_data}}'
        );
        
        $this->dropColumn('{{%parent_data}}', 'parent_ref_id');
    }
}
