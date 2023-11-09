<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210726_094452_add_index_to_admission_agreement_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            '{{%idx-admission_agreement-speciality_id}}',
            '{{%admission_agreement}}',
            'speciality_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx-admission_agreement-speciality_id', '{{%admission_agreement}}');
    }
}
