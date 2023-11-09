<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210302_122901_change_level_id_for_education_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropForeignKey('fk_education_data_education_level', '{{%education_data}}');

        $this->addForeignKey(
            '{{%fk-education_data-education_level_id}}',
            '{{%education_data}}',
            'education_level_id',
            '{{%education_level_reference_type}}',
            'id',
            'NO ACTION'
        );

    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-education_data-education_level_id}}', '{{%education_data}}');
        $this->addForeignKey('fk_education_data_education_level', '{{%education_data}}', 'education_level_id', '{{%dictionary_education_level}}', 'id', 'restrict', 'restrict');
    }

}
