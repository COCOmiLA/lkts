<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200930_111849_add_owner_id_column_to_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment}}', 'owner_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-attachment-owner_id}}',
            '{{%attachment}}',
            'owner_id'
        );

        
        $this->addForeignKey(
            '{{%fk-attachment-owner_id}}',
            '{{%attachment}}',
            'owner_id',
            '{{%user}}',
            'id',
            'no action'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-attachment-owner_id}}',
            '{{%attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-attachment-owner_id}}',
            '{{%attachment}}'
        );

        $this->dropColumn('{{%attachment}}', 'owner_id');
    }
}
