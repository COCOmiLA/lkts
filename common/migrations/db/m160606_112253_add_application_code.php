<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160606_112253_add_application_code extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%dormitory_register}}', 'register_code', $this->string(100));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%dormitory_register}}', 'register_code');
        
        Yii::$app->db->schema->refresh();
    }
}
