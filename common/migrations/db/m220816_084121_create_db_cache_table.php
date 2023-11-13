<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220816_084121_create_db_cache_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $blob = $this->getDb()->getSchema()->createColumnSchemaBuilder('LONGBLOB');
        if (Yii::$app->db->driverName === 'pgsql') {
            $blob = $this->getDb()->getSchema()->createColumnSchemaBuilder('BYTEA');
        }
        $this->createTable('{{%db_cache}}', [
            'id' => $this->char(128)->notNull(),
            'expire' => $this->integer()->null(),
            'data' => $blob,
        ]);
        $this->addPrimaryKey('pk-cache', '{{%db_cache}}', 'id');
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%db_cache}}');
    }
}
