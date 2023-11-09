<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200520_115713_create_dictionary_available_document_types_for_concession_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%dictionary_available_document_types_for_concession}}',
            [
                'id' => $this->primaryKey(),
                'id_pk' => $this->string(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
                'id_subject' => $this->string(),
                'subject_type' => $this->string(),
                'document_type' => $this->string(),
                'scan_required' => $this->boolean(),
                'archive' => $this->boolean()->notNull()->defaultValue(false),
            ],
            $tableOptions
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_available_document_types_for_concession}}');
        Yii::$app->db->schema->refresh();
    }
}
