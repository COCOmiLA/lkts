<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210121_012504_add_language_id_column_to_personal_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%personal_data}}', 'language_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-personal_data-language_id}}',
            '{{%personal_data}}',
            'language_id'
        );

        
        $this->addForeignKey(
            '{{%fk-personal_data-language_id}}',
            '{{%personal_data}}',
            'language_id',
            '{{%dictionary_foreign_languages}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-personal_data-language_id}}',
            '{{%personal_data}}'
        );

        
        $this->dropIndex(
            '{{%idx-personal_data-language_id}}',
            '{{%personal_data}}'
        );

        $this->dropColumn('{{%personal_data}}', 'language_id');
    }
}
