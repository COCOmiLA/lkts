<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210118_091016_create_speciality_attachment_junction_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bachelor_speciality_attachment}}', [
            'id' => $this->primaryKey(),
            'bachelor_speciality_id' => $this->integer(),
            'attachment_id' => $this->integer(),
        ],$tableOptions);

        $this->createIndex(
            '{{%idx-bachelor_speciality_attachment-bachelor_speciality_id}}',
            '{{%bachelor_speciality_attachment}}',
            'bachelor_speciality_id'
        );

        $this->addForeignKey(
            '{{%fk-bachelor_speciality_attachment-bachelor_speciality_id}}',
            '{{%bachelor_speciality_attachment}}',
            'bachelor_speciality_id',
            '{{%bachelor_speciality}}',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            '{{%idx-bachelor_speciality_attachment-attachment_id}}',
            '{{%bachelor_speciality_attachment}}',
            'attachment_id'
        );

        $this->addForeignKey(
            '{{%fk-bachelor_speciality_attachment-attachment_id}}',
            '{{%bachelor_speciality_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-bachelor_speciality_attachment-bachelor_speciality_id}}',
            '{{%bachelor_speciality_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-bachelor_speciality_attachment-bachelor_speciality_id}}',
            '{{%bachelor_speciality_attachment}}'
        );

        $this->dropForeignKey(
            '{{%fk-bachelor_speciality_attachment-attachment_id}}',
            '{{%bachelor_speciality_attachment}}'
        );

        $this->dropIndex(
            '{{%idx-bachelor_speciality_attachment-attachment_id}}',
            '{{%bachelor_speciality_attachment}}'
        );

        $this->dropTable('{{%bachelor_speciality_attachment}}');
    }
}
