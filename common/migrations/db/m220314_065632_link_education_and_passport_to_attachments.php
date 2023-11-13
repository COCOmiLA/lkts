<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220314_065632_link_education_and_passport_to_attachments extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%education_attachment}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->notNull(),
            'education_id' => $this->integer()->notNull(),
        ]);
        
        $this->createIndex(
            '{{%idx-education_attachment-attachment_id}}',
            '{{%education_attachment}}',
            'attachment_id'
        );

        $this->addForeignKey(
            '{{%fk-education_attachment-attachment_id}}',
            '{{%education_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
        );

        $this->createIndex(
            '{{%idx-education_attachment-education_id}}',
            '{{%education_attachment}}',
            'education_id'
        );

        $this->addForeignKey(
            '{{%fk-education_attachment-education_id}}',
            '{{%education_attachment}}',
            'education_id',
            '{{%education_data}}',
            'id',
        );


        $this->createTable('{{%passport_attachment}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->notNull(),
            'passport_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-passport_attachment-attachment_id}}',
            '{{%passport_attachment}}',
            'attachment_id'
        );

        $this->addForeignKey(
            '{{%fk-passport_attachment-attachment_id}}',
            '{{%passport_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
        );

        $this->createIndex(
            '{{%idx-passport_attachment-passport_id}}',
            '{{%passport_attachment}}',
            'passport_id'
        );

        $this->addForeignKey(
            '{{%fk-passport_attachment-passport_id}}',
            '{{%passport_attachment}}',
            'passport_id',
            '{{%passport_data}}',
            'id',
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-education_attachment-attachment_id}}',
            '{{%education_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-education_attachment-attachment_id}}',
            '{{%education_attachment}}'
        );

        $this->dropForeignKey(
            '{{%fk-education_attachment-education_id}}',
            '{{%education_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-education_attachment-education_id}}',
            '{{%education_attachment}}'
        );

        $this->dropTable('{{%education_attachment}}');


        $this->dropForeignKey(
            '{{%fk-passport_attachment-attachment_id}}',
            '{{%passport_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-passport_attachment-attachment_id}}',
            '{{%passport_attachment}}'
        );

        $this->dropForeignKey(
            '{{%fk-passport_attachment-passport_id}}',
            '{{%passport_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-passport_attachment-passport_id}}',
            '{{%passport_attachment}}'
        );

        $this->dropTable('{{%passport_attachment}}');
    }
}
