<?php

use common\models\settings\TextSetting;
use yii\db\Migration;




class m211012_120442_add_text_settings extends Migration
{
    protected $settings = [
        [
            'name' => 'email_tooltip',
            'description' => 'Текст подсказки для поля Email',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'questionary_address_tooltip',
            'description' => 'Текст подсказки для блока "Адрес постоянной регистрации"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'questionary_actual_address_tooltip',
            'description' => 'Текст подсказки для блока "Адрес проживания"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'questionary_save_btn_tooltip',
            'description' => 'Текст подсказки для кнопки "Сохранить" на странице Анкеты поступающего',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'education_save_btn_tooltip',
            'description' => 'Текст подсказки для кнопки "Сохранить" на странице Образования',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'update_ia_tooltip',
            'description' => 'Текст подсказки для кнопки "Обновить из 1С" на странице Индивидуальных достижений',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'add_ia_tooltip',
            'description' => 'Текст подсказки для кнопки "Добавить" на странице Индивидуальных достижений',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'save_ia_tooltip',
            'description' => 'Текст подсказки для кнопки "Сохранить" на странице Индивидуальных достижений',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'add_target_tooltip',
            'description' => 'Текст подсказки для кнопки добавляющей целевое',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'add_olympiad_tooltip',
            'description' => 'Текст подсказки для кнопки добавляющей олимпиаду',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'add_benefit_tooltip',
            'description' => 'Текст подсказки для кнопки добавляющей льготу',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'benefit_checkbox_individual_value_tooltip',
            'description' => 'Текст подсказки для чекбокса Льгота в форме создания льготы',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'download_consent_tooltip',
            'description' => 'Текст подсказки ссылки на скачивание согласия на зачисление',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'confirm_entrant_test_set_tooltip',
            'description' => 'Текст подсказки для кнопки подтвердить набор вступительных испытаний',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'save_entrant_tests_tooltip',
            'description' => 'Текст подсказки для кнопки сохраняющей результаты вступительных испытаний',
            'value' => 'Нажмите для сохранения результатов',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'update_application_tooltip',
            'description' => 'Текст подсказки "обновить заявление из 1С"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
        [
            'name' => 'send_application_tooltip',
            'description' => 'Текст подсказки для кнопки "Подать заявление"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_TOOLTIPS
        ],
    ];

    


    public function safeUp()
    {
        foreach ($this->settings as $setting_attrs) {
            $setting = new TextSetting();
            $setting->attributes = $setting_attrs;
            $setting->save(false);
        }

        TextSetting::updateAll(['category' => TextSetting::CATEGORY_TOOLTIPS], [
            'name' => [
                'snils_tooltip',
                'parents_tooltip',
                'specialities_tooltip',
                'choose_specialities_tooltip',
                'created_app_status_tooltip',
                'sent_app_status_tooltip',
                'approved_app_status_tooltip',
                'not_approved_app_status_tooltip',
                'rejected_by_one_s_app_status_tooltip',
                'sent_after_approved_app_status_tooltip',
                'sent_after_not_approved_app_status_tooltip',
                'return_all_app_status_tooltip',
                'moderating_now_app_status_tooltip',
            ]
        ]);
    }

    


    public function safeDown()
    {
        foreach ($this->settings as $setting) {
            $to_delete = TextSetting::findOne([
                'name' => $setting['name'],
                'category' => $setting['category']
            ]);
            if ($to_delete != null) {
                $to_delete->delete();
            }
        }
    }

}
