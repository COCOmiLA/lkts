<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160711_065512_add_campaign_stage extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%campaign_info}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer()->notNull(),
            'finance_code' => $this->string(100)->notNull(),
            'eduform_code' => $this->string(100)->notNull(),
            'detail_group_code' => $this->string(100)->notNull(),
            'date_start' => $this->integer(),
            'date_final' => $this->integer(),
            'date_order' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%campaign_info}}');
        Yii::$app->db->schema->refresh();
    }

}
