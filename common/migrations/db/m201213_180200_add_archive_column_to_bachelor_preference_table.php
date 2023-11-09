<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\BachelorPreferences;




class m201213_180200_add_archive_column_to_bachelor_preference_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_preferences}}', 'archive', $this->boolean()->null());
        BachelorPreferences::updateAll([
            'archive' => false
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_preferences}}', 'archive');
    }
}
