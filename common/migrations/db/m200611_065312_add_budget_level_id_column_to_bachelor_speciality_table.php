<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200611_065312_add_budget_level_id_column_to_bachelor_speciality_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'budget_level_id', $this->integer());

        
        $this->createIndex(
            '{{%idx-bachelor_speciality-budget_level_id}}',
            '{{%bachelor_speciality}}',
            'budget_level_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_speciality-budget_level_id}}',
            '{{%bachelor_speciality}}',
            'budget_level_id',
            '{{%dictionary_budget_level}}',
            'id',
            'set null'
        );
    }

    


    public function down()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_speciality-budget_level_id}}',
            '{{%bachelor_speciality}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_speciality-budget_level_id}}',
            '{{%bachelor_speciality}}'
        );

        $this->dropColumn('{{%bachelor_speciality}}', 'budget_level_id');
    }
}
