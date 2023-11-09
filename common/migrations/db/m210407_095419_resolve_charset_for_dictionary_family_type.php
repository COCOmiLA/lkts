<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210407_095419_resolve_charset_for_dictionary_family_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if (\Yii::$app->db->driverName === 'mysql') {
            $this->execute("ALTER TABLE dictionary_family_type CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        }
    }

    


    public function safeDown()
    {
        return;
    }
}
