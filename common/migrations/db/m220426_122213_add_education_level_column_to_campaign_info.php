<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220426_122213_add_education_level_column_to_campaign_info extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $column = 'education_level_ref_id';
        $table = 'education_level_reference_type';

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

    


    public function safeDown()
    {
        $column = 'education_level_ref_id';

        $this->dropForeignKey(
            '{{%fk-campaign_info-'.$column.'}}',
            '{{%campaign_info}}'
        );

        
        $this->dropIndex(
            '{{%idx-campaign_info-'.$column.'}}',
            '{{%campaign_info}}'
        );

        $this->dropColumn('{{%campaign_info}}', $column);
    }
}
