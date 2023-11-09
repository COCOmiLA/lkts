<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210121_012231_add_gender_id_to_personal_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%personal_data}}', 'gender_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-personal_data-gender_id}}',
            '{{%personal_data}}',
            'gender_id'
        );

        
        $this->addForeignKey(
            '{{%fk-personal_data-gender_id}}',
            '{{%personal_data}}',
            'gender_id',
            '{{%dictionary_gender}}',
            'id',
            'NO ACTION'
        );

    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-personal_data-gender_id}}',
            '{{%personal_data}}'
        );

        
        $this->dropIndex(
            '{{%idx-personal_data-gender_id}}',
            '{{%personal_data}}'
        );

        $this->dropColumn('{{%personal_data}}', 'gender_id');
    }

    













}
