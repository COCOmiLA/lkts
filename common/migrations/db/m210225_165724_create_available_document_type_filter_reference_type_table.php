<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210225_165724_create_available_document_type_filter_reference_type_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;
    
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $table = 'available_document_type_filter';

        $this->createReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type', $tableOptions);
    }

    


    public function safeDown()
    {
        $table = 'available_document_type_filter';
        
        if (Yii::$app->db->schema->getTableSchema('{{%' . (empty($table) ? '' : $table . '_') . 'reference_type}}') !== null) {
            $this->dropReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type');
        }
    }
}
