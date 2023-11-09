<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200825_074738_resolve_charset_for_tables extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        if (\Yii::$app->db->driverName === 'mysql') {
            $this->execute("ALTER TABLE agreement_info CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
            $this->execute("ALTER TABLE agreement_decline CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
