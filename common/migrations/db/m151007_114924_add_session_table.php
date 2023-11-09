<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m151007_114924_add_session_table extends MigrationWithDefaultOptions
{
    public function up()
    {
        $this->createTable('{{%session}}', [
            'id' => $this->string(40)->notNull(),
            'expire' => $this->integer()->defaultValue(null),
            "data" => $this->binary(429496729),
        ]);
        $this->addPrimaryKey('pk_session', '{{%session}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%session}}');
    }

}
