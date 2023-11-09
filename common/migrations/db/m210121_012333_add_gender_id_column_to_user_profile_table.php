<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210121_012333_add_gender_id_column_to_user_profile_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%user_profile}}', 'gender_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-user_profile-gender_id}}',
            '{{%user_profile}}',
            'gender_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_profile-gender_id}}',
            '{{%user_profile}}',
            'gender_id',
            '{{%dictionary_gender}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-user_profile-gender_id}}',
            '{{%user_profile}}'
        );

        
        $this->dropIndex(
            '{{%idx-user_profile-gender_id}}',
            '{{%user_profile}}'
        );

        $this->dropColumn('{{%user_profile}}', 'gender_id');
    }
}
