<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;




class m201213_183309_add_archive_column_to_bachelor_target_reception_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_target_reception}}', 'archive', $this->boolean()->null());
        BachelorTargetReception::updateAll([
            'archive' => false
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_target_reception}}', 'archive');
    }
}
