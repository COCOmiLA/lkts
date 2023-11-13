<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220728_125641_change_default_text_settings extends MigrationWithDefaultOptions
{
    protected static $settings_to_change = [
        [
            'language' => 'ru', 
            'name' => 'applist_hint', 
            'order' => 2, 
            'default_value_old' => 'Выберите направления для поступления (максимум 3)',
            'default_value_new' => 'Выберите направления для поступления (максимум 10)',
        ],
        [
            'language' => 'ru', 
            'name' => 'application_agreement_info', 
            'order' => 0, 
            'default_value_old' => 'Внимание! Подача согласия на зачисление возможна только 2 раза',
            'default_value_new' => 'Внимание! Подача согласия на зачисление возможна только 5 раз'
        ],
    ];

    


    public function safeUp()
    {
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'value' => $setting['default_value_old']
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_new']
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
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'value' => $setting['default_value_new']
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }
    }
}
