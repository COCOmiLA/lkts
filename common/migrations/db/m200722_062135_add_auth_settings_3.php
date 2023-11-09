<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m200722_062135_add_auth_settings_3 extends MigrationWithDefaultOptions
{
   public function safeUp()
    {
        $this->insert('{{%auth_settings}}', [
            'name' => 'can_not_input_latin_fio',
            'value' => '1',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->delete('{{%auth_settings}}', ['name' => 'can_not_input_latin_fio']);
        Yii::$app->db->schema->refresh();
    }
}
