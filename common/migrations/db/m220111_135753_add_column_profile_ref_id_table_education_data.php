<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220111_135753_add_column_profile_ref_id_table_education_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            '{{%education_data}}',
            'profile_ref_id',
            $this->integer()->defaultValue(null)
        );

        $this->addForeignKey(
            'FK_to_profile_reference_type_from_education_data',
            '{{%education_data}}',
            'profile_ref_id',
            '{{%profile_reference_type}}',
            'id'
        );
        $this->createIndex('IDX_for__profile_reference_type_in_education_data', '{{%education_data}}', 'profile_ref_id');
    }

    


    public function safeDown()
    {
        $this->dropIndex('IDX_for__profile_reference_type_in_education_data', '{{%education_data}}');
        $this->dropForeignKey('FK_to_profile_reference_type_from_education_data', '{{%education_data}}');

        $this->dropColumn('{{%education_data}}', 'profile_ref_id');
    }
}
