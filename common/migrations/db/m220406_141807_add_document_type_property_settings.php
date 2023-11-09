<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220406_141807_add_document_type_property_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%document_type_properties_settings}}', [
            'id' => $this->primaryKey(),
            'document_type_id' => $this->integer()
        ]);
        $this->createIndex('idx_document_type_properties_settings', '{{%document_type_properties_settings}}', 'document_type_id');
        $this->addForeignKey('fk_document_type_properties_settings_document_type_id', '{{%document_type_properties_settings}}', 'document_type_id', '{{%dictionary_document_type}}', 'id', 'cascade', 'cascade');

        $this->createTable('{{%document_type_attribute_setting}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'is_used' => $this->boolean()->notNull(),
            'is_required' => $this->boolean()->notNull(),
            'properties_setting_id' => $this->integer()
        ]);
        $this->createIndex('idx_properties_setting_id', '{{%document_type_attribute_setting}}', 'properties_setting_id');
        $this->addForeignKey('fk_document_type_attribute_setting_properties_setting_id', '{{%document_type_attribute_setting}}', 'properties_setting_id', '{{%document_type_properties_settings}}', 'id', 'cascade', 'cascade');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_document_type_attribute_setting_properties_setting_id', '{{%document_type_attribute_setting}}');
        $this->dropIndex('idx_properties_setting_id', '{{%document_type_attribute_setting}}');

        $this->dropForeignKey('fk_document_type_properties_settings_document_type_id', '{{%document_type_properties_settings}}');
        $this->dropIndex('idx_document_type_properties_settings', '{{%document_type_properties_settings}}');

        $this->dropTable('{{%document_type_attribute_setting}}');
        $this->dropTable('{{%document_type_properties_settings}}');

    }
}
