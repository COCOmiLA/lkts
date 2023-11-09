<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220117_133743_create_checksum_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%checksum}}', [
            'id' => $this->primaryKey(),
            'param' => $this->string(),
            'path' => $this->string(5000),
            'checksum' => $this->string(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%checksum}}');
    }
}
