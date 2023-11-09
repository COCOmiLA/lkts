<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230117_131218_add_column_to_education_data extends MigrationWithDefaultOptions
{
    private const TN = '{{%education_data}}';

    


    public function safeUp()
    {
        $this->addColumn(
            self::TN,
            'original_from_epgu',
            $this->boolean()->defaultValue(false)
        );
    }

    


    public function safeDown()
    {
        $this->dropColumn(self::TN, 'original_from_epgu');
    }
}
