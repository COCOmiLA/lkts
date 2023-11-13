<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221125_101047_add_resubmit_permissions_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%resubmit_permissions}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
            'allow' => $this->boolean()->defaultValue(false),
        ]);
        $this->createIndex('idx-resubmit_permissions-user_id', '{{%resubmit_permissions}}', 'user_id');
        $this->addForeignKey('fk-resubmit_permissions-user_id', '{{%resubmit_permissions}}', 'user_id', '{{%user}}', 'id', 'CASCADE');

        $this->createIndex('idx-resubmit_permissions-type_id', '{{%resubmit_permissions}}', 'type_id');
        $this->addForeignKey('fk-resubmit_permissions-type_id', '{{%resubmit_permissions}}', 'type_id', '{{%application_type}}', 'id', 'CASCADE');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk-resubmit_permissions-type_id', '{{%resubmit_permissions}}');
        $this->dropIndex('idx-resubmit_permissions-type_id', '{{%resubmit_permissions}}');

        $this->dropForeignKey('fk-resubmit_permissions-user_id', '{{%resubmit_permissions}}');
        $this->dropIndex('idx-resubmit_permissions-user_id', '{{%resubmit_permissions}}');

        $this->dropTable('{{%resubmit_permissions}}');
    }
}
