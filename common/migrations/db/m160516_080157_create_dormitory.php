<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160516_080157_create_dormitory extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dormitory_list}}', [
            'id' => $this->primaryKey(),
            'code' => $this->integer()->notNull(),
            'name' => $this->string(1000)->notNull(),
        ], $tableOptions);
        
        $this->createTable('{{%dormitory_register}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'dormitory_id' => $this->integer()->notNull(),
            'date_start' => $this->string(100),
            'date_end' => $this->string(100),
            'people_count' => $this->string(100),
            'status' => $this->integer()->notNull(),
            'decline_comment' => $this->string(1000),
        ], $tableOptions);
        
        $this->insert('{{%dormitory_list}}', [
            'name' => 'Общежитие №1, ул. Ленина 1',
            'code' => '1',
        ]); 

        $this->insert('{{%dormitory_list}}', [
            'name' => 'Общежитие №2, ул. Кулакова 3',
            'code' => '2',
        ]); 

        $this->insert('{{%dormitory_list}}', [
            'name' => 'Общежитие №3, ул. Ленина 1а',
            'code' => '3',
        ]); 
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dormitory_list}}');
        $this->dropTable('{{%dormitory_register}}');
        Yii::$app->db->schema->refresh();
    }
}
