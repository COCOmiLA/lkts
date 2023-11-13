<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220111_124902_add_column_profile_ref_id_table_cget_entrance_test_set extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            '{{%cget_entrance_test_set}}',
            'profile_ref_id',
            $this->integer()->defaultValue(null)
        );

        $this->addForeignKey(
            'FK_to_profile_reference_type_from_cget_entrance_test_set',
            '{{%cget_entrance_test_set}}',
            'profile_ref_id',
            '{{%profile_reference_type}}',
            'id'
        );
        $this->createIndex('IDX_for__profile_reference_type_in_entrance_test_set', '{{%cget_entrance_test_set}}', 'profile_ref_id');
    }

    


    public function safeDown()
    {
        $this->dropIndex('IDX_for__profile_reference_type_in_entrance_test_set', '{{%cget_entrance_test_set}}');
        $this->dropForeignKey('FK_to_profile_reference_type_from_cget_entrance_test_set', '{{%cget_entrance_test_set}}');

        $this->dropColumn('{{%cget_entrance_test_set}}', 'profile_ref_id');
    }
}
