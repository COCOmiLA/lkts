<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160419_071055_add_admission_agreement extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%admission_agreement}}', [
            'id' => $this->primaryKey(),
            'speciality_id' => $this->integer()->notNull(),
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
        $this->dropTable('{{%admission_agreement}}');
        Yii::$app->db->schema->refresh();
    }
}
