<?php

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;
use common\models\settings\TextSetting;




class m221214_233510_fix_text_settings_default_values extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $to_update = [
            
            [
                'language' => 'ru',
                'name' => 'status_rejected_by1_c',
                'default_value_old' => 'Отклонено 1С',
                'default_value_new' => 'Ошибка системы при одобрении',
            ],
            [
                'language' => 'ru', 
                'name' => 'info_message_in_education_for_series', 
                'default_value_old' => 'Серия заполняется только для документов выданным до 2012 года',
                'default_value_new' => 'Серия заполняется только для документов, выданных до 2012 года'
            ],
            
        ];

        foreach ($to_update as $setting) {
            
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

        $to_add = [
            
            [
                'language' => 'ru', 
                'name' => 'need_update_app_from_one_s', 
                'default_value_old' => null,
                'default_value_new' => 'Заявление устарело. В Информационной системе вуза присутствует заявление с более поздней датой. Необходимо отклонить заявление для использования поступающим актуальных данных.'
            ],
            [
                'language' => 'ru', 
                'name' => 'need_update_questionary_from_one_s', 
                'default_value_old' => null,
                'default_value_new' => 'Заявление устарело. Различаются версии данных анкеты поступающего в Личном кабинете и Информационной системе вуза, поступающему необходимо актуализировать данные анкеты перед подачей.'
            ],
            [
                'language' => 'ru', 
                'name' => 'add_previous_passports_text', 
                'default_value_old' => null,
                'default_value_new' => 'Просим указывать данные всех документов, удостоверяющих личность (в том числе предыдущих). Для паспорта РФ также просим прикреплять скан-копию 19 страницы. Это необходимо для корректной проверки результатов ЕГЭ.'
            ],
            [
                'language' => 'ru', 
                'name' => 'can_send_app_message', 
                'default_value_old' => null,
                'default_value_new' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Отправить в приемную комиссию"'
            ],
            [
                'language' => 'ru', 
                'name' => 'new_application_apply_notification_title', 
                'default_value_old' => null,
                'default_value_new' => 'Подано новое заявление в приёмную кампанию {campaignName}'
            ],
            [
                'language' => 'ru', 
                'name' => 'new_application_apply_notification_body', 
                'default_value_old' => null,
                'default_value_new' => 'В приёмную кампанию {campaignName} подано новое заявление от {applicantName} ({applicantEmail})'
            ],
            [
                'language' => 'ru', 
                'name' => 'several_achievements_with_same_group_chosen', 
                'default_value_old' => null,
                'default_value_new' => 'Выбрано несколько индивидуальных достижений из одной группы. Баллы будут учитываться только за одно индивидуальное достижение из группы.'
            ],
            [
                'language' => 'ru', 
                'name' => 'tooltip_for_link_to_created_application', 
                'default_value_old' => null,
                'default_value_new' => ''
            ],
        ];

        foreach ($to_add as $setting) {
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
        }

        $texts_to_delete = [
            'Сноска под кнопкой Заполнить анкету/подать заявление',
            'Сообщение пользователю о том, что анкета на проверке у модератора',
            'Текст предупреждения о сбросе статуса при изменении данных анкеты',
            'Сообщение пользователю о том, что ЕГЭ на проверке у модератора',
            'Сообщение о том, что результаты ЕГЭ сохранены',
            'Сообщение пользователю о том, что результаты ЕГЭ проверены модератором и поданы в 1С',
            'Сообщение пользователю о том, что результаты ЕГЭ поданы в 1С (песочница выключена)',
            'Сообщение пользователю о том, что результаты ЕГЭ отклонены модератором',
            'Текст об отсутствии доступных для записи вступительных испытаний или дат экзаменов',
            'Текст о невозможности записи на экзамены по причине проверки заявления модератором',
            'Текст снизу в Экзаменах',
            'Сообщение пользователю о том, что результаты ЕГЭ отклонены 1С',
            'Сообщение пользователю об ошибке, связанной с отстутсвием прикрепленного согласия на зачисление',
            'Текст, напоминающий о необходимости нажать на кнопку "Подать заявление"',
            'Текст об использовании порядковых номеров при выборе направлений',
            'Текст о существующих ограничениях на запись в общежитие',
            'Текст cверху в Общежитиях',
            'Текст снизу в Общежитиях',
            'Сообщение модератору о том, что возникла ошибка при сохранении в 1С',
            'Текст подсказки для кнопки добавляющей олимпиаду',
            'Текст подсказки для чекбокса Льгота в форме создания льготы',
            'Текст подсказки для кнопки "Подать заявление"',
        ];
        foreach ($texts_to_delete as $text_description_to_delete) {
            $this->delete('{{%text_settings}}', [
                'description' => $text_description_to_delete
            ]);
        }
    }
}
