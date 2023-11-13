<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200707_083016_remove_i_was_here extends MigrationWithDefaultOptions
{

    public function up()
    {
        $this->dropTable('{{%i_was_here}}');
    }

    public function down()
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
}
