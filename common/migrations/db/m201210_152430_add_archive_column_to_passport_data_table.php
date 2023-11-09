<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\PassportData;




class m201210_152430_add_archive_column_to_passport_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%passport_data}}', 'archive', $this->boolean()->null());
        PassportData::updateAll([
            'archive' => false
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%passport_data}}', 'archive');
    }
}
