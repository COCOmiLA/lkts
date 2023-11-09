<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210625_001351_change_table_charset extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if (\Yii::$app->db->driverName === 'mysql') {
            $this->execute(" ALTER TABLE admission_agreement_to_delete CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        }
    }

    


    public function safeDown()
    {
        return true;
    }
}
