<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190430_104417__debuggingsoap_ extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190430_104417__debuggingsoap_ cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->createTable('{{%debuggingsoap}}', [
            'id' => $this->primaryKey(),
            'debugging_enable' => $this->integer()->defaultValue(0)
        ]);

        $this->insert('{{%debuggingsoap}}', [
            'debugging_enable' => 0
        ]);
        
        Yii::$app->db->schema->refresh();
    }

    public function down()
    {
        $this->dropTable('{{%debuggingsoap}}');
        Yii::$app->db->schema->refresh();
    }
}
