<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220620_143259_fix_escaped_default_texts extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $fixed_settings = [
            ['language' => 'ru', 'name' => 'load_from_1c_info', 'order' => 0, 'default_value' => 'Получение информации из "1С:Университет ПРОФ" возможно после одобрения заявления модератором'],
            ['language' => 'ru', 'name' => 'can_send_app_message_if_first_try', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Подать заявление"'],
            ['language' => 'ru', 'name' => 'can_send_app_message_if_second_attempt', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Обновить заявление"'],
        ];
        
        foreach ($fixed_settings as $setting) {
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }
    }

    


    public function safeDown()
    {
        $escaped_settings = [
            ['language' => 'ru', 'name' => 'load_from_1c_info', 'order' => 0, 'default_value' => 'Получение информации из \"1С:Университет ПРОФ\" возможно после одобрения заявления модератором'],
            ['language' => 'ru', 'name' => 'can_send_app_message_if_first_try', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Подать заявление\"'],
            ['language' => 'ru', 'name' => 'can_send_app_message_if_second_attempt', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Обновить заявление\"'],
        ];
        
        foreach ($escaped_settings as $setting) {
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }
    }
}
