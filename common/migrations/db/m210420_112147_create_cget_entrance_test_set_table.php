<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210420_112147_create_cget_entrance_test_set_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        
        $this->createTable(
            '{{%cget_entrance_test_set}}',
            [
                'id' => $this->primaryKey(),
                'dictionary_competitive_group_entrance_test_id' => $this->integer()->defaultValue(null),

                'education_type_ref_id' => $this->integer()->defaultValue(null),
                'entrance_test_set_ref_id' => $this->integer()->defaultValue(null),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );

        $this->createIndex(
            "idx-cget-dictionary_competitive_group_entrance_test",
            'cget_entrance_test_set',
            'dictionary_competitive_group_entrance_test_id'
        );
        $this->addForeignKey(
            "fk-cget-dictionary_competitive_group_entrance_test",
            'cget_entrance_test_set',
            'dictionary_competitive_group_entrance_test_id',
            'dictionary_competitive_group_entrance_test',
            'id',
            'NO ACTION'
        );

        $refTables = [
            'education_type_ref_id' => 'dictionary_education_type',
            'entrance_test_set_ref_id' => 'subject_set_reference_type',
        ];

        foreach ($refTables as $refId => $refTable) {
            $tableName = Yii::$app->db->tablePrefix . $refTable;
            if (Yii::$app->db->getTableSchema($tableName, true) === null) {
                $this->createReferenceTable($refTable, $tableOptions);
            }
            $this->createIndex(
                "idx-cget-{$refTable}",
                'cget_entrance_test_set',
                $refId
            );
            $this->addForeignKey(
                "fk-cget-{$refTable}",
                'cget_entrance_test_set',
                $refId,
                $refTable,
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        $refTables = [
            'subject_set_reference_type',
            'dictionary_education_type',
        ];

        foreach ($refTables as $refTable) {

            $this->dropForeignKey(
                "fk-cget-{$refTable}",
                'cget_entrance_test_set'
            );
            $this->dropIndex(
                "idx-cget-{$refTable}",
                'cget_entrance_test_set'
            );
        }

        $this->dropTable('{{%cget_entrance_test_set}}');
    }
}
