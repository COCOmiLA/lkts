<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160524_133031_add_application_type extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%admission_campaign}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(1000)->notNull(),
        ], $tableOptions);
        
        $this->createTable('{{%application_type}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer()->notNull(),
            'name' => $this->string(1000)->notNull(),
        ], $tableOptions);
        
        $this->addColumn('{{%bachelor_application}}','type_id', $this->integer()->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%admission_campaign}}');
        $this->dropTable('{{%application_type}}');
        $this->dropColumn('{{%bachelor_application}}','type_id');
        
         Yii::$app->db->schema->refresh();
    }
}
