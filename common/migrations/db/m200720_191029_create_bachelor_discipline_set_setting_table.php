<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m200720_191029_create_bachelor_discipline_set_setting_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->createTable('{{%bachelor_discipline_set_setting}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer()->null(),
            'speciality_id' => $this->integer()->null(),
            'parent_discipline_id' => $this->integer()->null(),
            'is_using' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
        ]);

        
        $this->createIndex(
            '{{%idx-bachelor_discipline_set_setting-discipline_id}}',
            '{{%bachelor_discipline_set_setting}}',
            'discipline_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_discipline_set_setting-discipline_id}}',
            '{{%bachelor_discipline_set_setting}}',
            'discipline_id',
            '{{%dictionary_ege_discipline}}',
            'id',
            'SET NULL'
        );

        
        $this->createIndex(
            '{{%idx-bachelor_discipline_set_setting-speciality_id}}',
            '{{%bachelor_discipline_set_setting}}',
            'speciality_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_discipline_set_setting-speciality_id}}',
            '{{%bachelor_discipline_set_setting}}',
            'speciality_id',
            '{{%bachelor_speciality}}',
            'id',
            'CASCADE'
        );

    }

    


    public function down()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_discipline_set_setting-discipline_id}}',
            '{{%bachelor_discipline_set_setting}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_discipline_set_setting-discipline_id}}',
            '{{%bachelor_discipline_set_setting}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-bachelor_discipline_set_setting-speciality_id}}',
            '{{%bachelor_discipline_set_setting}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_discipline_set_setting-speciality_id}}',
            '{{%bachelor_discipline_set_setting}}'
        );

        $this->dropTable('{{%bachelor_discipline_set_setting}}');
    }
}
