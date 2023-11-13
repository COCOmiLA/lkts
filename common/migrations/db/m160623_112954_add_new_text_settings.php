<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160623_112954_add_new_text_settings extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'indach_save_error',
            'description' => 'Текст об ошибке сохранения индивидуального достижения',
            'value' => 'Возникла ошибка сохранения, заполните все обязательные поля или попробуйте повторить позднее.',
            'category' => 8,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'application_apply_reminder',
            'description' => 'Текст, напоминающий о необходимости нажать на кнопку "Подать заявление"',
            'value' => 'Не забудьте нажать кнопку "Подать заявление", когда завершите выбор направлений подготовки.',
            'category' => 4,
        ]);
                
        $this->insert('{{%text_settings}}', [
            'name' => 'application_agreement_info',
            'description' => 'Текст об ограничении количества поданных согласий',
            'value' => 'Внимание! Подача согласия на зачисление возможна только 2 раза',
            'category' => 4,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'dormitory_limitations',
            'description' => 'Текст о существующих ограничениях на запись в общежитие',
            'value' => 'Запись в общежитие возможна только после предоставления оригиналов документов в приёмную комиссию и только для поступающих проживающих вне Москвы и Московской области.',
            'category' => 6,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'education_save_success',
            'description' => 'Текст об успешном сохранении сведений об образовании',
            'value' => 'Сведения об образовании успешно сохранены на портале',
            'category' => 5,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'exam_register_notavailable',
            'description' => 'Текст об отсутствии доступных для записи вступительных испытаний или дат экзаменов',
            'value' => 'Запись на экзамены невозможна, так как нет доступных вступительных испытаний или дат экзаменов',
            'category' => 3,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'exam_register_blocked',
            'description' => 'Текст о невозможности записи на экзамены по причине проверки заявления модератором',
            'value' => 'Запись на экзамены невозможна, так как заявление находится на проверке у модератора',
            'category' => 3,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'footer_info',
            'description' => 'Текст в футере',
            'value' => '',
            'category' => 0,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'techworks_message',
            'description' => 'Сообщения о технических работах',
            'value' => 'Извините, в данный момент проводятся технические работы.<br>Повторите попытку подачи заявления позже.',
            'category' => 0,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'register_link_text',
            'description' => 'Тексты ссылки "Регистрация" на странице авторизации',
            'value' => 'Хотите подать заявление? Зарегистрируйтесь.',
            'category' => 0,
        ]);
        
        $this->insert('{{%text_settings}}', [
            'name' => 'createacc_link_text',
            'description' => 'Тексты ссылки на создание пароля на странице авторизации',
            'value' => 'Уже подали заявление? Получите пароль от личного кабинета',
            'category' => 0,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%text_settings}}',[
            'name' => [
                'indach_save_error',
                'application_apply_reminder',
                'application_agreement_info',
                'dormitory_limitations',
                'education_save_success',
                'exam_register_notavailable',
                'exam_register_blocked',
                'footer_info',
                'techworks_message',
                'register_link_text',
                'createacc_link_text',
            ]
        ]);
    }
}
