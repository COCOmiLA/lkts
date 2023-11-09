<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160615_064733_add_education_info extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_education_info}}', [
            'id' => $this->primaryKey(),
            'education_type_code' => $this->string(100)->notNull(),
            'document_type_code' => $this->string(100)->notNull(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_education_info}}');
        Yii::$app->db->schema->refresh();
    }
}
