<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m171228_140337_update_education_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_education_data_document_type', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_document_type', '{{%education_data}}', 'document_type_id', '{{%dictionary_document_type}}', 'id', 'restrict', 'restrict');

        $this->dropForeignKey('fk_education_data_education_level', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_education_level', '{{%education_data}}', 'education_level_id', '{{%dictionary_education_level}}', 'id', 'restrict', 'restrict');

        $this->dropForeignKey('fk_education_data_education_type', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_education_type', '{{%education_data}}', 'education_type_id', '{{%dictionary_education_type}}', 'id', 'restrict', 'restrict');

        \Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_education_data_document_type', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_document_type', '{{%education_data}}', 'document_type_id', '{{%dictionary_document_type}}', 'id', 'cascade', 'cascade');

        $this->dropForeignKey('fk_education_data_education_level', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_education_level', '{{%education_data}}', 'education_level_id', '{{%dictionary_education_level}}', 'id', 'cascade', 'cascade');

        $this->dropForeignKey('fk_education_data_education_type', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_education_type', '{{%education_data}}', 'education_type_id', '{{%dictionary_education_type}}', 'id', 'cascade', 'cascade');

        \Yii::$app->db->schema->refresh();
    }
}
