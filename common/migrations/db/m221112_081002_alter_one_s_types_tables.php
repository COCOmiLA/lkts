<?php

use common\components\Migration\SafeMigration;




class m221112_081002_alter_one_s_types_tables extends SafeMigration
{
    


    public function safeUp()
    {
        $this->renameColumn('dictionary_available_document_types_for_concession', 'id_pk', 'campaign_code');
        $this->createTable('{{%achievements_document_filter_junction}}', [
            'id' => $this->primaryKey(),
            'individual_achievement_document_type_id' => $this->integer()->notNull(),
            'available_document_type_filter_ref_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'achievements_document_junction_ia_document_id',
            '{{%achievements_document_filter_junction}}',
            'individual_achievement_document_type_id'
        );
        $this->createIndex(
            'achievements_document_junction_document_filter_id',
            '{{%achievements_document_filter_junction}}',
            'available_document_type_filter_ref_id'
        );
        $this->addForeignKey(
            'achievements_document_junction_ia_document_id',
            '{{%achievements_document_filter_junction}}',
            'individual_achievement_document_type_id',
            '{{%individual_achievements_document_types}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'achievements_document_junction_document_filter_id',
            '{{%achievements_document_filter_junction}}',
            'available_document_type_filter_ref_id',
            '{{%available_document_type_filter_reference_type}}',
            'id',
            'CASCADE'
        );

        $this->createTable('{{%document_type_for_concession_filter_junction}}', [
            'id' => $this->primaryKey(),
            'subject_type' => $this->string(),
            'available_document_type_for_concession_id' => $this->integer()->notNull(),
            'available_document_type_filter_ref_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'document_concession_filter_junction_document_type_id',
            '{{%document_type_for_concession_filter_junction}}',
            'available_document_type_for_concession_id'
        );
        $this->createIndex(
            'document_type_for_concession_filter_id',
            '{{%document_type_for_concession_filter_junction}}',
            'available_document_type_filter_ref_id'
        );
        $this->addForeignKey(
            'document_type_for_concession_filter_junction_doc_type_id',
            '{{%document_type_for_concession_filter_junction}}',
            'available_document_type_for_concession_id',
            '{{%dictionary_available_document_types_for_concession}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'document_type_for_concession_filter_junction_document_filter_id',
            '{{%document_type_for_concession_filter_junction}}',
            'available_document_type_filter_ref_id',
            '{{%available_document_type_filter_reference_type}}',
            'id',
            'CASCADE'
        );

        $this->db->schema->refresh();
        

        if (($tableSchema = $this->getTableSchema('individual_achievements_document_types')) === null) {
            return;
        }
        $columns = array_keys($tableSchema->columns);
        if (in_array('available_document_type_filter_ref_id', $columns)) {
            $this->db->createCommand("
                INSERT INTO
                    achievements_document_filter_junction (
                        individual_achievement_document_type_id,
                        available_document_type_filter_ref_id
                    )
                SELECT
                    id,
                    available_document_type_filter_ref_id
                FROM
                    individual_achievements_document_types
                WHERE
                    available_document_type_filter_ref_id IS NOT NULL
            ")->execute();
        }

        if (($tableSchema = $this->getTableSchema('dictionary_available_document_types_for_concession')) === null) {
            return;
        }
        $columns = array_keys($tableSchema->columns);
        if (
            in_array('subject_type', $columns) &&
            in_array('available_document_type_filter_ref_id', $columns)
        ) {
            $this->db->createCommand("
                INSERT INTO
                    document_type_for_concession_filter_junction (
                        subject_type,
                        available_document_type_for_concession_id,
                        available_document_type_filter_ref_id
                    )
                SELECT
                    subject_type,
                    id,
                    available_document_type_filter_ref_id
                FROM
                    dictionary_available_document_types_for_concession
                WHERE
                    available_document_type_filter_ref_id IS NOT NULL
            ")->execute();
        }

        $this->dropForeignKey(
            '{{%fk-ind_ach_document_types-available_document_type_filter_ref_id}}',
            '{{%individual_achievements_document_types}}'
        );

        $this->dropIndex(
            '{{%idx-ind_ach_document_types-available_document_type_filter_ref_id}}',
            '{{%individual_achievements_document_types}}'
        );
        $this->dropColumn('{{%individual_achievements_document_types}}', 'available_document_type_filter_ref_id');
        $this->dropForeignKey(
            '{{%fk-docs_concession-available_document_type_filter_ref_id}}',
            '{{%dictionary_available_document_types_for_concession}}'
        );

        $this->dropIndex(
            '{{%idx-docs_concession-available_document_type_filter_ref_id}}',
            '{{%dictionary_available_document_types_for_concession}}'
        );
        $this->dropColumn('{{%dictionary_available_document_types_for_concession}}', 'available_document_type_filter_ref_id');
        $this->dropColumn('{{%dictionary_available_document_types_for_concession}}', 'subject_type');
    }

    


    public function safeDown()
    {
        $this->renameColumn('dictionary_available_document_types_for_concession', 'campaign_code', 'id_pk');
        $this->addColumn('{{%individual_achievements_document_types}}', 'available_document_type_filter_ref_id', $this->integer()->null());
        $this->addColumn('{{%dictionary_available_document_types_for_concession}}', 'available_document_type_filter_ref_id', $this->integer()->null());
        $this->addColumn('{{%dictionary_available_document_types_for_concession}}', 'subject_type', $this->string());

        $this->dropForeignKey(
            'achievements_document_junction_ia_document_id',
            '{{%achievements_document_filter_junction}}'
        );
        $this->dropForeignKey(
            'achievements_document_junction_document_filter_id',
            '{{%achievements_document_filter_junction}}'
        );
        $this->dropIndex(
            'achievements_document_junction_ia_document_id',
            '{{%achievements_document_filter_junction}}'
        );
        $this->dropIndex(
            'achievements_document_junction_document_filter_id',
            '{{%achievements_document_filter_junction}}'
        );
        $this->dropTable('{{%achievements_document_filter_junction}}');

        $this->dropForeignKey(
            'document_type_for_concession_filter_junction_doc_type_id',
            '{{%document_type_for_concession_filter_junction}}'
        );
        $this->dropForeignKey(
            'document_type_for_concession_filter_junction_document_filter_id',
            '{{%document_type_for_concession_filter_junction}}'
        );
        $this->dropIndex(
            'document_concession_filter_junction_document_type_id',
            '{{%document_type_for_concession_filter_junction}}'
        );
        $this->dropIndex(
            'document_type_for_concession_filter_id',
            '{{%document_type_for_concession_filter_junction}}'
        );
        $this->dropTable('{{%document_type_for_concession_filter_junction}}');
    }
}
