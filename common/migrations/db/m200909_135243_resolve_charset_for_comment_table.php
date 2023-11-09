<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200909_135243_resolve_charset_for_comment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if (\Yii::$app->db->driverName === 'mysql') {
            $this->execute("ALTER TABLE comments_coming CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
        }
    }

    


    public function safeDown()
    {
        return;
    }

    













}
