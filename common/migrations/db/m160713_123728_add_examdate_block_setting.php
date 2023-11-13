<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160713_123728_add_examdate_block_setting extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%examdate_block}}', [
            'id' => $this->primaryKey(),
            'application_type_id' => $this->integer()->notNull(),
            'hours' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%examdate_block}}');
        Yii::$app->db->schema->refresh();
    }
}
