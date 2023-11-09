<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190218_130835_create_news_recaptcha_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('recaptcha', [
            'id' => $this->primaryKey(),
            'recaptcha_signup' => $this->integer()->notNull()->defaultValue(0),
            'recaptcha_login' => $this->integer()->notNull()->defaultValue(0),
            'recaptcha_abit_access' => $this->integer()->notNull()->defaultValue(0)
        ]);

        $this->insert('recaptcha', [
            'recaptcha_signup' => '0',
            'recaptcha_login' => '0',
            'recaptcha_abit_access' => '0'
        ]);
    }

    


    public function down()
    {
        $this->dropTable('recaptcha');
    }
}
