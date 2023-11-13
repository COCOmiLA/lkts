<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200610_124735_add_budget_code_id_column_to_bachelor_speciality_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'budget_level', $this->integer());

        
        $this->createIndex(
            '{{%idx-bachelor_speciality-budget_level}}',
            '{{%bachelor_speciality}}',
            'budget_level'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_speciality-budget_level}}',
            '{{%bachelor_speciality}}',
            'budget_level',
            '{{%dictionary_budget_level}}',
            'id',
            'SET NULL'
        );
    }

    


    public function down()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_speciality-budget_level}}',
            '{{%bachelor_speciality}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_speciality-budget_level}}',
            '{{%bachelor_speciality}}'
        );

        $this->dropColumn('{{%bachelor_speciality}}', 'budget_level');
    }
}
