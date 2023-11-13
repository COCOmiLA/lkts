<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210128_092047_add_ref_keys_to_dictionary_individual_achievement extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'campaign_ref_id' => 'admission_campaign_reference_type',
            'ach_category_ref_id' => 'achievement_category_reference_type',
        ];


        foreach ($columns as $column => $table) {

            $this->addColumn('{{%dictionary_individual_achievement}}', $column, $this->integer());

            $this->createIndex(
                '{{%idx-dictionary_individual_achievement-' . $column . '}}',
                '{{%dictionary_individual_achievement}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-dictionary_individual_achievement-' . $column . '}}',
                '{{%dictionary_individual_achievement}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }


        $this->addColumn('{{%individual_achievements_document_types}}', 'document_type_ref_id', $this->integer());

        $this->createIndex(
            '{{%idx-indiieves_document_-achievemet_type_ref_id}}',
            '{{%individual_achievements_document_types}}',
            'document_type_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-indivevets_docment_types-ment_document_type_id}}',
            '{{%individual_achievements_document_types}}',
            'document_type_ref_id',
            '{{%dictionary_document_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {

        $this->dropForeignKey(
            '{{%fk-indivevets_docment_types-ment_document_type_id}}',
            '{{%individual_achievements_document_types}}'
        );

        $this->dropIndex(
            '{{%idx-indiieves_document_-achievemet_type_ref_id}}',
            '{{%individual_achievements_document_types}}'
        );

        $this->dropColumn('{{%individual_achievements_document_types}}', 'document_type_ref_id');

        $columns = [
            'campaign_ref_id' => 'admission_campaign_reference_type',
            'ach_category_ref_id' => 'achievement_category_reference_type',
        ];

        foreach ($columns as $column => $table) {
            $this->dropForeignKey(
                '{{%fk-dictionary_individual_achievement-' . $column . '}}',
                '{{%dictionary_individual_achievement}}'
            );

            $this->dropIndex(
                '{{%idx-dictionary_individual_achievement-' . $column . '}}',
                '{{%dictionary_individual_achievement}}'
            );

            $this->dropColumn('{{%dictionary_individual_achievement}}', $column);
        }
    }

}
