<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220121_145649_add_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'belarusian_citizenship_code',
            'description' => 'Код гражданства Республика Беларусь',
            'value' => '',
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => ['belarusian_citizenship_code']]);

        Yii::$app->db->schema->refresh();
    }
}
