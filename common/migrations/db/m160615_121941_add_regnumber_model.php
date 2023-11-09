<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160615_121941_add_regnumber_model extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%user_regnumber}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'registration_number' => $this->string("100")->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_user_regnumber', '{{%user_regnumber}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_regnumber}}');
        Yii::$app->db->schema->refresh();
    }
}
