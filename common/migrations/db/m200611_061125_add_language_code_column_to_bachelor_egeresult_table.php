<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200611_061125_add_language_code_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'language_id', $this->integer());

        
        $this->createIndex(
            '{{%idx-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}',
            'language_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}',
            'language_id',
            '{{%foreign_languages_for_ege}}',
            'id',
            'SET NULL'
        );
    }

    


    public function down()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_egeresult-language_id}}',
            '{{%bachelor_egeresult}}'
        );

        $this->dropColumn('{{%bachelor_egeresult}}', 'language_id');
    }
}
