<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221005_080329_alter_collation_in_fias_doma extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci';
            $this->db->createCommand("ALTER TABLE `dictionary_fias_doma` CONVERT TO {$tableOptions}")->execute();
        }
    }
}
