<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220408_113540_create_agreement_record_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%agreement_records}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'date' => $this->integer(),
            'speciality_name' => $this->string(),
            'speciality_guid' => $this->string(),
            'application_id' => $this->integer(),
        ]);
        $this->createIndex('idx_agreement_records_application', '{{%agreement_records}}', 'application_id');
        $this->addForeignKey('fk_agreement_records_application', '{{%agreement_records}}', 'application_id', '{{%bachelor_application}}', 'id', 'cascade', 'cascade');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_agreement_records_application', '{{%agreement_records}}');
        $this->dropIndex('idx_agreement_records_application', '{{%agreement_records}}');

        $this->dropTable('{{%agreement_records}}');
    }
}
