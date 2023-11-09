<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160615_115524_set_nullable_email extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%user}}', 'email', $this->string());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%user}}', 'email', $this->string()->notNull());
        Yii::$app->db->schema->refresh();
    }
}
