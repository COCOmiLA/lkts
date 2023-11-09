<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m200522_065541_add_columns_to_bachelor_speciality_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'preference_id', $this->integer());
        $this->addColumn('{{%bachelor_speciality}}', 'target_reception_id', $this->integer());

        
        $this->createIndex(
            '{{%idx-bachelor_speciality-preference_id}}',
            '{{%bachelor_speciality}}',
            'preference_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_speciality-preference_id}}',
            '{{%bachelor_speciality}}',
            'preference_id',
            '{{%bachelor_preferences}}',
            'id',
            'SET NULL'
        );

        
        $this->createIndex(
            '{{%idx-bachelor_speciality-target_reception_id}}',
            '{{%bachelor_speciality}}',
            'target_reception_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_speciality-target_reception_id}}',
            '{{%bachelor_speciality}}',
            'target_reception_id',
            '{{%bachelor_target_reception}}',
            'id',
            'SET NULL'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_speciality-preference_id}}',
            '{{%bachelor_speciality}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_speciality-preference_id}}',
            '{{%bachelor_speciality}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-bachelor_speciality-target_reception_id}}',
            '{{%bachelor_speciality}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_speciality-target_reception_id}}',
            '{{%bachelor_speciality}}'
        );

        $this->dropColumn('{{%bachelor_speciality}}', 'preference_id');
        $this->dropColumn('{{%bachelor_speciality}}', 'target_reception_id');
    }
}
