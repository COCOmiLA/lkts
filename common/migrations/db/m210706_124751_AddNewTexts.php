<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210706_124751_AddNewTexts extends MigrationWithDefaultOptions
{
    protected $settings = [
        [
            'name' => 'snils_tooltip',
            'description' => 'Текст подсказки для поля СНИЛС',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_QUESTIONARY
        ],
        [
            'name' => 'parents_tooltip',
            'description' => 'Текст подсказки для блока "Данные родителей или законных представителей"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_QUESTIONARY
        ],
        [
            'name' => 'specialities_tooltip',
            'description' => 'Текст подсказки для блока "Добавленные направления"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_APPLICATION
        ],
        [
            'name' => 'choose_specialities_tooltip',
            'description' => 'Текст подсказки для блока "Добавление направлений подготовки в заявление"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_APPLICATION
        ],
        [
            'name' => 'created_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Не подано"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'sent_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Подано впервые"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'approved_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Принято"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'not_approved_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Отклонено"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'rejected_by_one_s_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Отклонено 1С"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'sent_after_approved_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Подано после одобрения"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'sent_after_not_approved_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Подано после отклонения"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'return_all_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "Отозвано"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
        [
            'name' => 'moderating_now_app_status_tooltip',
            'description' => 'Текст подсказки статуса заявления "На проверке"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ],
    ];

    


    public function safeUp()
    {
        foreach ($this->settings as $setting_attrs) {
            $setting = new TextSetting();
            $setting->attributes = $setting_attrs;
            $setting->save(false);
        }

        $this->addColumn('{{%attachment_type}}', 'tooltip_description', $this->string(1000));

    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'tooltip_description');

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
