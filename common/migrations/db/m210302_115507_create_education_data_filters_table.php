<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210302_115507_create_education_data_filters_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%education_data_filters}}', [
            'id' => $this->primaryKey(),

            'education_level_id' => $this->integer(),
            'education_type_id' => $this->integer(),
            'document_type_id' => $this->integer(),
            'actual' => $this->boolean(),
            'period' => $this->string(),
        ], $tableOptions);
        $this->createIndex('idx-education_data_filters-education_level_id', '{{%education_data_filters}}', 'education_level_id');
        $this->createIndex('idx-education_data_filters-education_type_id', '{{%education_data_filters}}', 'education_type_id');
        $this->createIndex('idx-education_data_filters-document_type_id', '{{%education_data_filters}}', 'document_type_id');

        $this->addForeignKey(
            '{{%fk-education_data_filters-education_level_id}}',
            '{{%education_data_filters}}',
            'education_level_id',
            '{{%education_level_reference_type}}',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            '{{%fk-education_data_filters-education_type_id}}',
            '{{%education_data_filters}}',
            'education_type_id',
            '{{%dictionary_education_type}}',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            '{{%fk-education_data_filters-document_type_id}}',
            '{{%education_data_filters}}',
            'document_type_id',
            '{{%dictionary_document_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-education_data_filters-education_level_id}}',
            '{{%education_data_filters}}'
        );
        $this->dropForeignKey(
            '{{%fk-education_data_filters-education_type_id}}',
            '{{%education_data_filters}}'
        );
        $this->dropForeignKey(
            '{{%fk-education_data_filters-document_type_id}}',
            '{{%education_data_filters}}'
        );

        $this->dropIndex('idx-education_data_filters-education_level_id', '{{%education_data_filters}}');
        $this->dropIndex('idx-education_data_filters-education_type_id', '{{%education_data_filters}}');
        $this->dropIndex('idx-education_data_filters-document_type_id', '{{%education_data_filters}}');

        $this->dropTable('{{%education_data_filters}}');
    }
}
