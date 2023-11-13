<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210121_013730_add_ref_id_column_to_user_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'user_ref_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-user-user_ref_id}}',
            '{{%user}}',
            'user_ref_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user-user_ref_id}}',
            '{{%user}}',
            'user_ref_id',
            '{{%user_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-user-user_ref_id}}',
            '{{%user}}'
        );

        
        $this->dropIndex(
            '{{%idx-user-user_ref_id}}',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'user_ref_id');
    }
}
