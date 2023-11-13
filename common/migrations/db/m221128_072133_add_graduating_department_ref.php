<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221128_072133_add_graduating_department_ref extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'graduating_department_ref_id', $this->integer());
        $this->createIndex(
            '{{%idx-dictionary_speciality-graduating_department_ref_id}}',
            '{{%dictionary_speciality}}',
            'graduating_department_ref_id'
        );


        $this->addForeignKey(
            '{{%fk-dictionary_speciality-graduating_department_ref_id}}',
            '{{%dictionary_speciality}}',
            'graduating_department_ref_id',
            '{{%subdivision_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-dictionary_speciality-graduating_department_ref_id}}',
            '{{%dictionary_speciality}}'
        );
        $this->dropIndex('{{%idx-dictionary_speciality-graduating_department_ref_id}}', '{{%dictionary_speciality}}');

        $this->dropColumn('{{%dictionary_speciality}}', 'graduating_department_ref_id');
    }
}
