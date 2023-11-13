<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210422_105835_add_columns_to_bachelor_speciality extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('bachelor_speciality', 'cget_entrance_test_set_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'FK_to_cget_entrance_test_set',
            'bachelor_speciality',
            'cget_entrance_test_set_id',
            'cget_entrance_test_set',
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_to_cget_entrance_test_set', 'bachelor_speciality');

        $this->dropColumn('bachelor_speciality', 'cget_entrance_test_set_id');
    }
}
