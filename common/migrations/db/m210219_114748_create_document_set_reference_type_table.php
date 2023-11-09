<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210219_114748_create_document_set_reference_type_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;
    
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tables = ['document_set'];

        foreach ($tables as $table) {
            $this->createReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type', $tableOptions);
        }
    }

    


    public function safeDown()
    {
        $tables = ['document_set'];
        
        foreach ($tables as $table) {
            if (Yii::$app->db->schema->getTableSchema('{{%' . (empty($table) ? '' : $table . '_') . 'reference_type}}') !== null) {
                $this->dropReferenceTable( (empty($table) ? '' : $table . '_') . 'reference_type');
            }
        }
    }
}
