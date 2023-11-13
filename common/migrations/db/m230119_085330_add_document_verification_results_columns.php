<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m230119_085330_add_document_verification_results_columns extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    private const TABLES_TO_CHANGE = [
        '{{%passport_data}}',
        '{{%education_data}}',
        '{{%bachelor_preferences}}',
        '{{%individual_achievement}}',
        '{{%bachelor_target_reception}}',
        '{{%bachelor_result_centralized_testing}}',
    ];

    private const REF_TYPE_TN = '{{%document_check_status_reference_type}}';

    


    public function safeUp()
    {
        $this->createReferenceTable(self::REF_TYPE_TN);

        foreach (self::TABLES_TO_CHANGE as $tableName) {
            $this->addColumn(
                $tableName,
                'document_check_status_ref_id',
                $this->integer()->defaultValue(null)
            );
            $this->addColumn(
                $tableName,
                'read_only',
                $this->boolean()->defaultValue(false)
            );

            $normalTablename = $this->normalizeTablename($tableName);
            $this->createIndex(
                "IDX-$normalTablename-doc_check_status_ref_id",
                $tableName,
                'document_check_status_ref_id'
            );
            $this->addForeignKey(
                "FK-$normalTablename-doc_check_status_ref_id",
                $tableName,
                'document_check_status_ref_id',
                self::REF_TYPE_TN,
                'id',
                'NO ACTION'
            );
        }

        $this->db->schema->refresh();
    }

    


    public function safeDown()
    {
        foreach (self::TABLES_TO_CHANGE as $tableName) {
            $normalTablename = $this->normalizeTablename($tableName);
            $this->dropForeignKey("FK-$normalTablename-doc_check_status_ref_id", $tableName);
            $this->dropIndex("IDX-$normalTablename-doc_check_status_ref_id", $tableName);

            $this->dropColumn($tableName, 'read_only');
            $this->dropColumn($tableName, 'document_check_status_ref_id');
        }
        $this->dropReferenceTable(self::REF_TYPE_TN);

        $this->db->schema->refresh();
    }
}
