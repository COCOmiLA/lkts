<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210420_114746_create_cget_child_subject_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        
        $this->createTable(
            '{{%cget_child_subject}}',
            [
                'id' => $this->primaryKey(),
                'cget_entrance_test_id' => $this->integer()->defaultValue(null),

                'child_subject_index' => $this->integer()->defaultValue(null),

                'child_subject_ref_id' => $this->integer()->defaultValue(null),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );

        $this->createIndex(
            "idx-cget_child_subject-cget_entrance_test",
            'cget_child_subject',
            'cget_entrance_test_id'
        );
        $this->addForeignKey(
            "fk-cget_child_subject-cget_entrance_test",
            'cget_child_subject',
            'cget_entrance_test_id',
            'cget_entrance_test',
            'id',
            'NO ACTION'
        );
        $refTables = ['child_subject_ref_id' => 'discipline_reference_type'];

        foreach ($refTables as $refId => $refTable) {
            $tableName = Yii::$app->db->tablePrefix . $refTable;
            if (Yii::$app->db->getTableSchema($tableName, true) === null) {
                $this->createReferenceTable($refTable, $tableOptions);
            }
            $this->createIndex(
                "idx-cget_child_subject-{$refTable}",
                'cget_child_subject',
                $refId
            );
            $this->addForeignKey(
                "fk-cget_child_subject-{$refTable}",
                'cget_child_subject',
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
            "fk-cget_child_subject-cget_entrance_test",
            'cget_child_subject'
        );
        $this->dropIndex(
            "idx-cget_child_subject-cget_entrance_test",
            'cget_child_subject'
        );
        $refTables = ['discipline_reference_type'];

        foreach ($refTables as $refTable) {
            $this->dropForeignKey(
                "fk-cget_child_subject-{$refTable}",
                'cget_entrance_test'
            );
            $this->dropIndex(
                "idx-cget_child_subject-{$refTable}",
                'cget_entrance_test'
            );
        }

        $this->dropTable('{{%cget_child_subject}}');
    }
}
