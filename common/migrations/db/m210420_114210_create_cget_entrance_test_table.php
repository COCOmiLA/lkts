<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210420_114210_create_cget_entrance_test_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        
        $this->createTable(
            '{{%cget_entrance_test}}',
            [
                'id' => $this->primaryKey(),
                'cget_entrance_test_set_id' => $this->integer()->defaultValue(null),

                'priority' => $this->integer()->defaultValue(null),
                'min_score' => $this->integer()->defaultValue(null),

                'subject_ref_id' => $this->integer()->defaultValue(null),
                'entrance_test_result_source_ref_id' => $this->integer()->defaultValue(null),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );

        $this->createIndex(
            "idx-cget-cget_entrance_test_set",
            'cget_entrance_test',
            'cget_entrance_test_set_id'
        );
        $this->addForeignKey(
            "fk-cget-cget_entrance_test_set",
            'cget_entrance_test',
            'cget_entrance_test_set_id',
            'cget_entrance_test_set',
            'id',
            'NO ACTION'
        );

        $refTables = [
            'subject_ref_id' => 'discipline_reference_type',
            'entrance_test_result_source_ref_id' => 'discipline_form_reference_type',
        ];

        foreach ($refTables as $refId => $refTable) {
            $tableName = Yii::$app->db->tablePrefix . $refTable;
            if (Yii::$app->db->getTableSchema($tableName, true) === null) {
                $this->createReferenceTable($refTable, $tableOptions);
            }
            $this->createIndex(
                "idx-cget-{$refTable}",
                'cget_entrance_test',
                $refId
            );
            $this->addForeignKey(
                "fk-cget-{$refTable}",
                'cget_entrance_test',
                $refId,
                $refTable,
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {

        $this->dropForeignKey(
            "fk-cget-cget_entrance_test_set",
            'cget_entrance_test'
        );
        $this->dropIndex(
            "idx-cget-cget_entrance_test_set",
            'cget_entrance_test'
        );

        $refTables = [
            'discipline_reference_type',
            'discipline_form_reference_type',
        ];

        foreach ($refTables as $refTable) {
            $this->dropForeignKey(
                "fk-cget-{$refTable}",
                'cget_entrance_test'
            );
            $this->dropIndex(
                "idx-cget-{$refTable}",
                'cget_entrance_test'
            );
        }

        $this->dropTable('{{%cget_entrance_test}}');
    }
}
