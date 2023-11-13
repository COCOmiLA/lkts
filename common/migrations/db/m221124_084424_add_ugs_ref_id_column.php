<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221124_084424_add_ugs_ref_id_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'ugs_ref_id', $this->integer());
        $this->createIndex(
            '{{%idx-dictionary_speciality-ugs_ref_id}}',
            '{{%dictionary_speciality}}',
            'ugs_ref_id'
        );


        $this->addForeignKey(
            '{{%fk-dictionary_speciality-ugs_ref_id}}',
            '{{%dictionary_speciality}}',
            'ugs_ref_id',
            '{{%ugs_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-dictionary_speciality-ugs_ref_id}}',
            '{{%dictionary_speciality}}'
        );
        $this->dropIndex('{{%idx-dictionary_speciality-ugs_ref_id}}', '{{%dictionary_speciality}}');

        $this->dropColumn('{{%dictionary_speciality}}', 'ugs_ref_id');
    }
}
