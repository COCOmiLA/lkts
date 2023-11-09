<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210121_055528_add_ref_columns_to_campaign_info_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'education_source_ref_id' => 'education_source',
            'education_form_ref_id' => 'education_form',
            'admission_category_id' => 'dictionary_admission_categories',
            'detail_group_ref_id' => 'detail_group',
        ];


        foreach ($columns as $column => $table_start) {
            if ($table_start != "dictionary_admission_categories") {
                $table = $table_start . '_reference_type';
            } else {
                $table = $table_start;

            }

            $this->addColumn('{{%campaign_info}}', $column, $this->integer()->null());

            $this->createIndex(
                '{{%idx-campaign_info-' . $column . '}}',
                '{{%campaign_info}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-campaign_info-' . $column . '}}',
                '{{%campaign_info}}',
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
            'education_source_ref_id',
            'education_form_ref_id',
            'admission_category_id',
            'detail_group_ref_id',
        ];

        foreach ($columns as $column) {
            
                $this->dropForeignKey(
                '{{%fk-campaign_info-' . $column . '}}',
                '{{%campaign_info}}'
            );

                
                $this->dropIndex(
                    '{{%idx-campaign_info-' . $column . '}}',
                    '{{%campaign_info}}'
                );

            $this->dropColumn('{{%campaign_info}}', $column);
        }
    }
}
