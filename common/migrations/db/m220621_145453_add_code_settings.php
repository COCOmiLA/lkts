<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220621_145453_add_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'foreign_passport_guid',
            'description' => 'Код паспорта иностранного гражданина',
            'value' => '',
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => ['foreign_passport_guid']]);

        Yii::$app->db->schema->refresh();
    }
}
