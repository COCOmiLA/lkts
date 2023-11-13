<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210507_184031_add_new_debug_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'enable_api_debug', $this->boolean()->defaultValue(false));

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'enable_api_debug');

        Yii::$app->db->schema->refresh();
    }

    













}
