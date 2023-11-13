<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m210420_095024_create_dictionary_competitive_group_entrance_test_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%dictionary_competitive_group_entrance_test}}',
            [
                'id' => $this->primaryKey(),
                'campaign_ref_id' => $this->integer()->defaultValue(null),
                'curriculum_ref_id' => $this->integer()->defaultValue(null),
                'competitive_group_ref_id' => $this->integer()->defaultValue(null),

                'updated_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->defaultValue(null),

                'archive' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );

        
        $refTables = [
            'campaign_ref_id' => 'admission_campaign_reference_type',
            'curriculum_ref_id' => 'curriculum_reference_type',
            'competitive_group_ref_id' => 'competitive_group_reference_type',
        ];

        foreach ($refTables as $refId => $refTable) {
            $tableName = Yii::$app->db->tablePrefix . $refTable;
            if (Yii::$app->db->getTableSchema($tableName, true) === null) {
                $this->createReferenceTable($refTable, $tableOptions);
            }
            $this->createIndex(
                "idx-cget-{$refTable}",
                'dictionary_competitive_group_entrance_test',
                $refId
            );
            $this->addForeignKey(
                "fk-cget-{$refTable}",
                'dictionary_competitive_group_entrance_test',
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
            'curriculum_reference_type',
            'competitive_group_reference_type',
            'admission_campaign_reference_type',
        ];

        foreach ($refTables as $refTable) {

            $this->dropForeignKey(
                "fk-cget-{$refTable}",
                'dictionary_competitive_group_entrance_test'
            );
            $this->dropIndex(
                "idx-cget-{$refTable}",
                'dictionary_competitive_group_entrance_test'
            );
        }

        $this->dropTable('{{%dictionary_competitive_group_entrance_test}}');
    }
}
