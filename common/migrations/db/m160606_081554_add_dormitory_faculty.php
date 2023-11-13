<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160606_081554_add_dormitory_faculty extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->delete('{{%dormitory_list}}');
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dormitory_faculty}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(1000)->notNull(),
        ], $tableOptions);
        
        $this->addColumn('{{%dormitory_list}}', 'faculty_id', $this->integer()->notNull());
        $this->alterColumn('{{%dormitory_list}}', 'code', $this->string(100)->notNull());
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%dormitory_list}}', 'faculty_id');
        $this->alterColumn('{{%dormitory_list}}', 'code', $this->integer()->notNull());
        $this->dropTable('{{%dormitory_faculty}}');
        
        Yii::$app->db->schema->refresh();
    }
}
