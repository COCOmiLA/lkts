<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m221028_115222_fix_escaped_text_settings extends MigrationWithDefaultOptions
{
    protected static $settings_to_change = [
        [
            'language' => 'ru',
            'name' => 'load_from_1c_info',
            'default_value_old' => 'Получение информации из \"1С:Университет ПРОФ\" возможно после одобрения заявления модератором',
            'default_value_new' => 'Получение информации из "1С:Университет ПРОФ" возможно после одобрения заявления модератором',
        ],
        [
            'language' => 'ru',
            'name' => 'can_send_app_message_if_first_try',
            'default_value_old' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Подать заявление\"',
            'default_value_new' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Подать заявление"',
        ],
        [
            'language' => 'ru',
            'name' => 'can_send_app_message_if_second_attempt',
            'default_value_old' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Обновить заявление\"',
            'default_value_new' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Обновить заявление"',
        ]
    ];

    public function safeUp()
    {
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'value' => $setting['default_value_old']
            ]);

            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }
    }

    


    public function safeDown()
    {
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'value' => $setting['default_value_new']
            ]);

            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }
    }
}
