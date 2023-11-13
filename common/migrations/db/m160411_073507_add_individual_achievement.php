<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160411_073507_add_individual_achievement extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%individual_achievement}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'dictionary_individual_achievement_id'=>$this->integer()->notNull(),
            'document_type_id'=>$this->integer()->notNull(),
            'document_series'=>$this->string(100),
            'document_number'=>$this->string(100),
            'document_date'=>$this->string(100),
            'document_giver'=>$this->string(1000),
            'file'=>$this->string(1000)->notNull(),
            'filename' => $this->string(255),
            'extension' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%individual_achievement}}');
        Yii::$app->db->schema->refresh();
    }
}
