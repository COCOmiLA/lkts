<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200804_092614_add_enable_dummy_soap_mode extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'enable_dummy_soap_mode', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();
    }


    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'enable_dummy_soap_mode');

        Yii::$app->db->schema->refresh();
    }

}
