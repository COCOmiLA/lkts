<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210726_094540_add_index_to_education_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            '{{%idx-education_data-application_id}}',
            '{{%education_data}}',
            'application_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx-education_data-application_id', '{{%education_data}}');
    }
}
