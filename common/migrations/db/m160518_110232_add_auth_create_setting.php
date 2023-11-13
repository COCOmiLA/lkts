<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160518_110232_add_auth_create_setting extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
         $this->insert('{{%auth_settings}}', [
            'name' => 'use_email',
            'value' => '0',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->delete('{{%auth_settings}}', ['name' => 'use_email']);
        Yii::$app->db->schema->refresh();
    }
}
