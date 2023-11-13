<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230517_095256_add_status_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $value = 'Подан отказ от зачисления';
        $this->insert('{{%text_settings}}', [
            'name' => 'status_enrollment_rejection_requested', 
            'description' => "Текст статуса заявления \"{$value}\"",
            'value' => $value,
            'category' => 'application',
            'application_type' => 0,
            'language' => 'ru',
            'tooltip_description' => 'Статус используется для заявлений с поданным отказом от зачисления',
            'default_value' =>$value
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', ['name' => 'status_enrollment_rejection_requested']);
    }
}
