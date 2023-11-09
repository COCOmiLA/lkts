<?php

namespace common\models\settings;

class TextSetting extends Setting
{
    const CATEGORY_ALL = 'all';
    const CATEGORY_INDEX = 'index';
    const CATEGORY_QUESTIONARY = 'questionary';
    const CATEGORY_EXAMS = 'exams';
    const CATEGORY_APPLICATION = 'application';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_SANDBOX = 'sandbox';
    const CATEGORY_INDACH = 'individual_achievements';
    const CATEGORY_BENEFITS = 'benefits';
    const CATEGORY_SCANS = 'scans';
    const CATEGORY_STATUSES = 'statuses';
    const CATEGORY_TOOLTIPS = 'tooltips';
    const CATEGORY_ALL_APPLICATIONS = 'all_applications';
    const CATEGORY_NOTIFICATIONS = 'notifications';
    
    const APPLICATION_TYPE_DEFAULT = 0;

    


    public static function tableName()
    {
        return '{{%text_settings}}';
    }

    public function rules()
    {
        return [
            [
                'name',
                'required'
            ],
            [
                'name',
                'string',
                'max' => 100
            ],
            [
                [
                    'value',
                    'description',
                    'tooltip_description'
                ],
                'string',
                'max' => 1000
            ],
            [
                'category',
                'string',
                'max' => 50
            ]
        ];
    }

    public static function getCategories()
    {
        return [
            self::CATEGORY_ALL => 'Без категории',
            self::CATEGORY_INDEX => 'Главная страница',
            self::CATEGORY_QUESTIONARY => 'Анкета',
            self::CATEGORY_EXAMS => 'Экзамены',
            self::CATEGORY_APPLICATION => 'Заявление (направления подготовки)',
            self::CATEGORY_EDUCATION => 'Образование',
            self::CATEGORY_SANDBOX => 'Песочница',
            self::CATEGORY_INDACH => 'Индивидуальные достижения',
            self::CATEGORY_TOOLTIPS => 'Подсказки',
            self::CATEGORY_BENEFITS => 'Особые условия поступления',
            self::CATEGORY_SCANS => 'Скан-Копии документов',
            self::CATEGORY_STATUSES => 'Статусы',
            self::CATEGORY_ALL_APPLICATIONS => 'Все заявления',
            self::CATEGORY_NOTIFICATIONS => 'Уведомления',
        ];
    }
}
