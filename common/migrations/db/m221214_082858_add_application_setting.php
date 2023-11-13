<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221214_082858_add_application_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%applications_settings}}', [
            'name' => 'use_one_s_settings_for_fields_to_be_required',
            'description' => 'Использовать настройки обязательности заполнения полей документов из 1С:Университет ПРОФ',
            'type' => 'checkbox',
            'value' => '0',
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%applications_settings}}', ['name' => 'use_one_s_settings_for_fields_to_be_required']);
    }
}
