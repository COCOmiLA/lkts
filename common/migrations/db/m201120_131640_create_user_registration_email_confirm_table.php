<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m201120_131640_create_user_registration_email_confirm_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_registration_email_confirm}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'status' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);

        
        $this->createIndex(
            '{{%idx-user_registration_email_confirm-user_id}}',
            '{{%user_registration_email_confirm}}',
            'user_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_registration_email_confirm-user_id}}',
            '{{%user_registration_email_confirm}}',
            'user_id',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-user_registration_email_confirm-user_id}}',
            '{{%user_registration_email_confirm}}'
        );

        
        $this->dropIndex(
            '{{%idx-user_registration_email_confirm-user_id}}',
            '{{%user_registration_email_confirm}}'
        );

        $this->dropTable('{{%user_registration_email_confirm}}');
    }
}
