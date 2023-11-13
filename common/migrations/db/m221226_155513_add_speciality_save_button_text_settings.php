<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221226_155513_add_speciality_save_button_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $to_add = [
            [                
                'name' => 'save_speciality_button_label',
                'description' => 'Подпись кнопки сохранения информации о направлениях подготовки',
                'value' => 'Сохранить информацию о направлениях подготовки',
                'category' => 'application',
                'application_type' => 0,
                'language' => 'ru',
                'default_value' => 'Сохранить информацию о направлениях подготовки'
            ],
            [
                'name' => 'save_speciality_button_label_step_forward',
                'description' => 'Подпись кнопки сохранения информации о направлениях подготовки с переходом к следующему шагу',
                'value' => 'Сохранить информацию о направлениях подготовки и перейти к следующему шагу',
                'category' => 'application',
                'application_type' => 0,
                'language' => 'ru',
                'default_value' => 'Сохранить информацию о направлениях подготовки и перейти к следующему шагу'
            ],
            [
                'name' => 'save_speciality_scans_button_label',
                'description' => 'Подпись кнопки сохранения информации о предоставленных скан-копиях',
                'value' => 'Сохранить информацию о предоставленных скан-копиях',
                'category' => 'application',
                'application_type' => 0,
                'language' => 'ru',
                'default_value' => 'Сохранить информацию о предоставленных скан-копиях'
            ],
            [
                'name' => 'save_speciality_scans_button_label_step_forward',
                'description' => 'Подпись кнопки сохранения информации о предоставленных скан-копиях с переходом к следующему шагу',
                'value' => 'Сохранить информацию о предоставленных скан-копиях и перейти к следующему шагу',
                'category' => 'application',
                'application_type' => 0,
                'language' => 'ru',
                'default_value' => 'Сохранить информацию о предоставленных скан-копиях и перейти к следующему шагу'
            ]
        ];

        foreach ($to_add as $text_setting) {
            if (!$this->isExists($text_setting)) {
                $this->insert('{{%text_settings}}', $text_setting);
            } else {
                Yii::error("Текст {$text_setting['name']} уже существует");
            }
        }
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => [
                'save_speciality_button_label',
                'save_speciality_button_label_step_forward',
                'save_speciality_scans_button_label',
                'save_speciality_scans_button_label_step_forward'
            ]
        ]);
    }

    protected function isExists(array $text_setting): bool
    {
        return (new Query())->from('{{%text_settings}}')->where([
            'name' => $text_setting['name'],
            'application_type' => $text_setting['application_type'],
            'language' => 'ru'
        ])->exists();
    }
}
