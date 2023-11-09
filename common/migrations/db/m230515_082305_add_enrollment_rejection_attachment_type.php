<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m230515_082305_add_enrollment_rejection_attachment_type extends MigrationWithDefaultOptions
{
    const SYSTEM_TYPE_ENROLLMENT_REJECTION = 9;

    


    public function safeUp()
    {
        $exists = (new Query())->from('{{%attachment_type}}')
            ->where(['system_type' => static::SYSTEM_TYPE_ENROLLMENT_REJECTION])
            ->exists();

        if (!$exists) {
            $this->insert('{{%attachment_type}}', [
                'name' => 'Бланк для отказа от зачисления',
                'required' => false,
                'related_entity' => 'application',
                'updated_at' => time(),
                'created_at' => time(),
                'document_type' => null,
                'is_using' => true,
                'hidden' => false,
                'system_type' => static::SYSTEM_TYPE_ENROLLMENT_REJECTION,
                'need_one_of_documents' => false,
                'allow_add_new_file_after_app_approve' => false,
                'allow_delete_file_after_app_approve' => false
            ]);
        }

        $this->createTable('{{%enrollment_rejection_attachment}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->null(),
            'bachelor_speciality_id' => $this->integer()->null(),
        ]);

        $this->createIndex('{{%idx_attachment_id}}', '{{%enrollment_rejection_attachment}}', 'attachment_id');
        $this->addForeignKey('{{%fk_enrollment_rejection_attachment}}', '{{%enrollment_rejection_attachment}}', 'attachment_id', '{{%attachment}}', 'id');

        $this->createIndex('{{%idx_bachelor_speciality_id}}', '{{%enrollment_rejection_attachment}}', 'bachelor_speciality_id');
        $this->addForeignKey('{{%fk_enrollment_rejection_spec}}', '{{%enrollment_rejection_attachment}}', 'bachelor_speciality_id', '{{%bachelor_speciality}}', 'id');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_enrollment_rejection_spec}}', '{{%enrollment_rejection_attachment}}');
        $this->dropIndex('{{%idx_bachelor_speciality_id}}', '{{%enrollment_rejection_attachment}}');

        $this->dropForeignKey('{{%fk_enrollment_rejection_attachment}}', '{{%enrollment_rejection_attachment}}');
        $this->dropIndex('{{%idx_attachment_id}}', '{{%enrollment_rejection_attachment}}');

        $this->dropTable('{{%enrollment_rejection_attachment}}');

        $this->delete('{{%attachment_type}}', ['system_type' => static::SYSTEM_TYPE_ENROLLMENT_REJECTION]);
    }
}
