<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200804_094923_create_dummy_soap_response_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext');
        if (Yii::$app->db->driverName === 'pgsql') {
            $text = $this->text();
        }
        $this->createTable('{{%dummy_soap_response}}', [
            'id' => $this->primaryKey(),
            'method_name' => $this->string()->unique(),
            'method_response' => $text,
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dummy_soap_response}}');
    }
}
