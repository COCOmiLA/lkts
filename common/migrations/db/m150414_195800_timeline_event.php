<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m150414_195800_timeline_event extends MigrationWithDefaultOptions
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%timeline_event}}', [
            'id' => $this->primaryKey(),
            'application' => $this->string(64)->notNull(),
            'category' => $this->string(64)->notNull(),
            'event' => $this->string(64)->notNull(),
            'data' => $this->text(),
            'created_at' => $this->integer()->notNull()
        ], $tableOptions);

        $this->createIndex('idx_created_at', '{{%timeline_event}}', 'created_at');
    }

    public function down()
    {
        $this->dropTable('{{%timeline_event}}');
    }
}
