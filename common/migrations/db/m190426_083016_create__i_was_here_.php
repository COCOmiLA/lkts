<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190426_083016_create__i_was_here_ extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190425_144306_creat__i_was_here_ cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%i_was_here}}', [
            'id'            => $this->primaryKey(),
            'umk'           => $this->integer()->defaultValue(0),
            'stipend'       => $this->integer()->defaultValue(0),
            'schedule'      => $this->integer()->defaultValue(0),
            'portfolio'     => $this->integer()->defaultValue(0),
            'evaluation'    => $this->integer()->defaultValue(0),
            'academicplan'  => $this->integer()->defaultValue(0),
            'graduateWork'  => $this->integer()->defaultValue(0),
            'user_id'       => $this->integer()->defaultValue(-1)
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%i_was_here}}');
    }
}
