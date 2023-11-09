<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210408_092056_add_code_setting_for_genders extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'male_code',
            'description' => 'Код мужского пола',
            'value' => '000000001',
        ]);

        $this->insert('{{%code_settings}}', [
            'name' => 'female_code',
            'description' => 'Код женского пола',
            'value' => '000000002',
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', ['name' => ['male_code', 'female_code']]);

        Yii::$app->db->schema->refresh();
    }
}
