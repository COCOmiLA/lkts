<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221012_170008_add_reference_type_columns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('deletion_mark')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'deletion_mark', $this->boolean()->defaultValue(false));
            $this->renameColumn('{{%dictionary_budget_level}}', 'deletion_mark', 'has_deletion_mark');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('predefined')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'predefined', $this->boolean()->defaultValue(false));
            $this->renameColumn('{{%dictionary_budget_level}}', 'predefined', 'is_predefined');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('predefined_data_name')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'predefined_data_name', $this->string(1000));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('is_folder')) {
            $this->addColumn('{{%dictionary_budget_level}}', 'is_folder', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('posted')) {
            $this->addColumn('{{%dictionary_budget_level}}', 'posted', $this->boolean()->defaultValue(false));
        }
        
        
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('is_folder')) {
            $this->addColumn('{{%dictionary_admission_base}}', 'is_folder', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('has_deletion_mark')) {
            $this->addColumn('{{%dictionary_admission_base}}', 'has_deletion_mark', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('posted')) {
            $this->addColumn('{{%dictionary_admission_base}}', 'posted', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('is_predefined')) {
            $this->addColumn('{{%dictionary_admission_base}}', 'is_predefined', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('predefined_data_name')) {
            $this->addColumn('{{%dictionary_admission_base}}', 'predefined_data_name', $this->string(1000));
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('short_name')) {
            $this->alterColumn('{{%dictionary_admission_base}}', 'short_name', $this->string(1000)->defaultValue(null));
        }
        
        
        
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('is_folder')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'is_folder', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('has_deletion_mark')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'has_deletion_mark', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('posted')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'posted', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('is_predefined')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'is_predefined', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('predefined_data_name')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'predefined_data_name', $this->string(1000));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('parent_key')) {
            $this->addColumn('{{%dictionary_document_shipment}}', 'parent_key', $this->string(255));
        }
        
        
        
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('is_folder')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'is_folder', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('has_deletion_mark')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'has_deletion_mark', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('posted')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'posted', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('is_predefined')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'is_predefined', $this->boolean()->defaultValue(false));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('predefined_data_name')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'predefined_data_name', $this->string(1000));
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('parent_key')) {
            $this->addColumn('{{%dictionary_ownage_form}}', 'parent_key', $this->string(255));
        }
        
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('has_deletion_mark')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'has_deletion_mark', $this->boolean());
            $this->renameColumn('{{%dictionary_budget_level}}', 'has_deletion_mark', 'deletion_mark');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('is_predefined')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'is_predefined', $this->boolean());
            $this->renameColumn('{{%dictionary_budget_level}}', 'is_predefined', 'predefined');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('predefined_data_name')) {
            $this->alterColumn('{{%dictionary_budget_level}}', 'predefined_data_name', $this->string(255));
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('is_folder')) {
            $this->dropColumn('{{%dictionary_budget_level}}', 'is_folder');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_budget_level}}')->getColumn('posted')) {
            $this->dropColumn('{{%dictionary_budget_level}}', 'posted');
        }
        
        
        
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('is_folder')) {
            $this->dropColumn('{{%dictionary_admission_base}}', 'is_folder');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('has_deletion_mark')) {
            $this->dropColumn('{{%dictionary_admission_base}}', 'has_deletion_mark');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('posted')) {
            $this->dropColumn('{{%dictionary_admission_base}}', 'posted');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('is_predefined')) {
            $this->dropColumn('{{%dictionary_admission_base}}', 'is_predefined');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('predefined_data_name')) {
            $this->dropColumn('{{%dictionary_admission_base}}', 'predefined_data_name');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_admission_base}}')->getColumn('short_name')) {
            $this->alterColumn('{{%dictionary_admission_base}}', 'short_name', $this->string(1000));
        }
        
        
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('is_folder')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'is_folder');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('has_deletion_mark')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'has_deletion_mark');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('posted')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'posted');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('is_predefined')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'is_predefined');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('predefined_data_name')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'predefined_data_name');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_document_shipment}}')->getColumn('parent_key')) {
            $this->dropColumn('{{%dictionary_document_shipment}}', 'parent_key');
        }
        
        
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('is_folder')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'is_folder');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('has_deletion_mark')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'has_deletion_mark');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('posted')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'posted');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('is_predefined')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'is_predefined');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('predefined_data_name')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'predefined_data_name');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%dictionary_ownage_form}}')->getColumn('parent_key')) {
            $this->dropColumn('{{%dictionary_ownage_form}}', 'parent_key');
        }
        
        \Yii::$app->db->schema->refresh();
    }
}
