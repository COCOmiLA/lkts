<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200611_060318_create_foreign_languages_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->createTable('{{%foreign_languages_for_ege}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer(),
            'name' => $this->string(),
            'code' => $this->string(),
        ]);

        
        $this->createIndex(
            '{{%idx-foreign_languages_for_ege-discipline_id}}',
            '{{%foreign_languages_for_ege}}',
            'discipline_id'
        );

        
        $this->addForeignKey(
            '{{%fk-foreign_languages_for_ege-discipline_id}}',
            '{{%foreign_languages_for_ege}}',
            'discipline_id',
            '{{%dictionary_ege_discipline}}',
            'id',
            'CASCADE'
        );
    }

    


    public function down()
    {
        
        $this->dropForeignKey(
            '{{%fk-foreign_languages_for_ege-discipline_id}}',
            '{{%foreign_languages_for_ege}}'
        );

        
        $this->dropIndex(
            '{{%idx-foreign_languages_for_ege-discipline_id}}',
            '{{%foreign_languages_for_ege}}'
        );

        $this->dropTable('{{%foreign_languages_for_ege}}');
    }
}
