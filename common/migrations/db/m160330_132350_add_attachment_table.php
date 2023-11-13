<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160330_132350_add_attachment_table extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%attachment}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer(),
            'application_id' => $this->integer(),
            'file' => $this->string(1000)->notNull(),
            'attachment_type' => $this->string(100)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
        
    }

    public function safeDown()
    {
        $this->dropTable('{{%attachment}}');
        
        Yii::$app->db->schema->refresh();
    }
}
