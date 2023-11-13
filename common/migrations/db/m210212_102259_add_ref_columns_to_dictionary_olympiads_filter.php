<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210212_102259_add_ref_columns_to_dictionary_olympiads_filter extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'campaign_ref_id' => 'admission_campaign_reference_type',
            'special_mark_id' => 'dictionary_special_marks',
            'olympiad_id' => 'dictionary_olympiads',
            'curriculum_ref_id' => 'curriculum_reference_type',
            'variant_of_retest_ref_id' => 'variant_of_retest_reference_type',
        ];


        foreach ($columns as $column => $table) {

            $this->addColumn('{{%dictionary_olympiads_filter}}', $column, $this->integer());

            $this->createIndex(
                '{{%idx-olympiads_filter-' . $column . '}}',
                '{{%dictionary_olympiads_filter}}',
                $column
            );


            $this->addForeignKey(
                '{{%fk-dictionary_olympiads_filter-' . $column . '}}',
                '{{%dictionary_olympiads_filter}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {

        $columns = [
            'campaign_ref_id' => 'admission_campaign_reference_type',
            'special_mark_id' => 'dictionary_special_marks',
            'olympiad_id' => 'dictionary_olympiads',
            'curriculum_ref_id' => 'curriculum_reference_type',
            'variant_of_retest_ref_id' => 'variant_of_retest_reference_type',
        ];

        foreach ($columns as $column => $table) {

            $this->dropForeignKey(
                '{{%fk-dictionary_olympiads_filter-' . $column . '}}',
                '{{%dictionary_olympiads_filter}}'
            );


            $this->dropIndex(
                '{{%idx-olympiads_filter-' . $column . '}}',
                '{{%dictionary_olympiads_filter}}'
            );

            $this->dropColumn('{{%dictionary_olympiads_filter}}', $column);
        }
    }

}
