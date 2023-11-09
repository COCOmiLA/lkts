<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\TextSettingsManager\TextSettingsManager;
use common\models\settings\TextSetting;
use yii\helpers\VarDumper;




class m220605_204934_add_default_value_column_to_text_settings extends MigrationWithDefaultOptions
{
    protected static $default_values = [
        ['language' => 'ru', 'name' => 'applist_hint', 'order' => 1, 'default_value' => 'Заполните анкету'],
        ['language' => 'ru', 'name' => 'applist_hint', 'order' => 2, 'default_value' => 'Выберите направления для поступления (максимум 3)'],
        ['language' => 'ru', 'name' => 'applist_hint', 'order' => 3, 'default_value' => 'Когда заявление проверят и примут или отклонят, вы получите уведомление по электронной почте'],
        ['language' => 'ru', 'name' => 'questionary_sended', 'order' => 0, 'default_value' => 'Анкета сохранена на портале'],
        ['language' => 'ru', 'name' => 'questionary_approved_sandbox_on', 'order' => 0, 'default_value' => 'Анкета проверена модератором и подана в приемную комиссию'],
        ['language' => 'ru', 'name' => 'questionary_approved_sandbox_off', 'order' => 0, 'default_value' => 'Анкета подана в приемную комиссию'],
        ['language' => 'ru', 'name' => 'questionary_notapproved', 'order' => 0, 'default_value' => 'Анкета была отклонена модератором'],
        ['language' => 'ru', 'name' => 'questionary_rejected_by1c', 'order' => 0, 'default_value' => 'Анкета была отклонена 1С'],
        ['language' => 'ru', 'name' => 'questionary_save_success', 'order' => 0, 'default_value' => 'Сохранение прошло успешно.'],
        ['language' => 'ru', 'name' => 'questionary_file_error', 'order' => 0, 'default_value' => 'Внимание! Вы не прикрепили обязательные копии документов:'],
        ['language' => 'ru', 'name' => 'save_error', 'order' => 0, 'default_value' => 'Возникла ошибка сохранения. Попробуйте повторить позднее.'],
        ['language' => 'ru', 'name' => 'moder_comment', 'order' => 0, 'default_value' => 'Комментарий проверяющего:'],
        ['language' => 'ru', 'name' => 'load_from_1c_info', 'order' => 0, 'default_value' => 'Получение информации из \"1С:Университет ПРОФ\" возможно после одобрения заявления модератором'],
        ['language' => 'ru', 'name' => 'application_blocked', 'order' => 0, 'default_value' => 'Заявление находится на проверке у модератора'],
        ['language' => 'ru', 'name' => 'application_sended', 'order' => 0, 'default_value' => 'Заявление отправлено и ожидает проверки модератором'],
        ['language' => 'ru', 'name' => 'application_approved_sandbox_on', 'order' => 0, 'default_value' => 'Заявление одобрено модератором и отправлено на рассмотрение в образовательную организацию'],
        ['language' => 'ru', 'name' => 'application_approved_sandbox_off', 'order' => 0, 'default_value' => 'Заявление отправлено на рассмотрение в образовательную организацию'],
        ['language' => 'ru', 'name' => 'application_notapproved', 'order' => 0, 'default_value' => 'Заявление было отклонено модератором'],
        ['language' => 'ru', 'name' => 'application_rejected_by1c', 'order' => 0, 'default_value' => 'Заявление было отклонено 1С'],
        ['language' => 'ru', 'name' => 'unsaved_leave', 'order' => 0, 'default_value' => 'На странице есть несохраненные изменения. Если вы покинете страницу без сохранения, они будут потеряны.'],
        ['language' => 'ru', 'name' => 'info_agreement', 'order' => 0, 'default_value' => 'Для прикрепления согласия на зачисление необходимо скачать бланк документа, распечатать его, заполнить, подписать, отсканировать и прикрепить обратно.'],
        ['language' => 'ru', 'name' => 'indach_save_error', 'order' => 0, 'default_value' => 'Возникла ошибка сохранения, заполните все обязательные поля или попробуйте повторить позднее.'],
        ['language' => 'ru', 'name' => 'application_agreement_info', 'order' => 0, 'default_value' => 'Внимание! Подача согласия на зачисление возможна только 2 раза'],
        ['language' => 'ru', 'name' => 'education_save_success', 'order' => 0, 'default_value' => 'Сведения об образовании успешно сохранены на портале'],
        ['language' => 'ru', 'name' => 'footer_info', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'techworks_message', 'order' => 0, 'default_value' => 'Извините, в данный момент проводятся технические работы.<br>Повторите попытку подачи заявления позже.'],
        ['language' => 'ru', 'name' => 'register_link_text', 'order' => 0, 'default_value' => 'Хотите подать заявление? Зарегистрируйтесь.'],
        ['language' => 'ru', 'name' => 'createacc_link_text', 'order' => 0, 'default_value' => 'Уже подали заявление? Получите пароль от личного кабинета'],
        ['language' => 'ru', 'name' => 'indach_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'indach_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'questionary_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'questionary_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'applications_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'applications_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'index_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'index_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'spec_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'spec_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'education_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'education_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'exam_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'access_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'access_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'login_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'login_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'register_top_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'register_bottom_text', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'block_top_text', 'order' => 0, 'default_value' => 'ВНИМАНИЕ!!! Может быть ограничена подача заявлений в соответствии с регламентом работы приемной комиссии.'],
        ['language' => 'ru', 'name' => 'questionary__create_from_1C', 'order' => 0, 'default_value' => 'Анкета была обновлена из ПК. Требуется проверить правильность внесённых данных.'],
        ['language' => 'ru', 'name' => 'benefits_before_spec', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'benefits_before_olymp', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'benefits_before_target', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'load_scans', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'reset_link_text', 'order' => 0, 'default_value' => 'Забыли пароль? Перейдите по ссылке'],
        ['language' => 'ru', 'name' => 'info_agreement_decline', 'order' => 0, 'default_value' => 'Для прикрепления отзыва согласия на зачисление необходимо скачать бланк документа, распечатать его, заполнить, подписать, отсканировать и прикрепить обратно.'],
        ['language' => 'ru', 'name' => 'snils_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'parents_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'specialities_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'choose_specialities_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'created_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'sent_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'approved_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'not_approved_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'rejected_by_one_s_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'sent_after_approved_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'sent_after_not_approved_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'return_all_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'moderating_now_app_status_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'no_required_attachment_text', 'order' => 0, 'default_value' => 'Отсутствует обязательный для прикрепления файл.'],
        ['language' => 'ru', 'name' => 'no_data_saved_text', 'order' => 0, 'default_value' => 'Изменения в данных не были обнаружены. Ничего не было сохранено.'],
        ['language' => 'ru', 'name' => 'global_text_for_ajax_tooltip', 'order' => 0, 'default_value' => 'Запрос обрабатывается...'],
        ['language' => 'ru', 'name' => 'global_text_for_submit_tooltip', 'order' => 0, 'default_value' => 'Запрос обрабатывается...'],
        ['language' => 'ru', 'name' => 'email_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'questionary_address_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'questionary_actual_address_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'questionary_save_btn_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'education_save_btn_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'update_ia_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'add_ia_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'save_ia_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'add_target_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'add_benefit_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'download_consent_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'confirm_entrant_test_set_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'save_entrant_tests_tooltip', 'order' => 0, 'default_value' => 'Нажмите для сохранения результатов'],
        ['language' => 'ru', 'name' => 'update_application_tooltip', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'text_for_set_when_speciality-bvi', 'order' => 0, 'default_value' => 'Вступительные испытания не требуются'],
        ['language' => 'ru', 'name' => 'status_created', 'order' => 0, 'default_value' => 'Не подано'],
        ['language' => 'ru', 'name' => 'status_sent', 'order' => 0, 'default_value' => 'Подано впервые'],
        ['language' => 'ru', 'name' => 'status_approved', 'order' => 0, 'default_value' => 'Принято'],
        ['language' => 'ru', 'name' => 'status_not_approved', 'order' => 0, 'default_value' => 'Отклонено'],
        ['language' => 'ru', 'name' => 'status_rejected_by1_c', 'order' => 0, 'default_value' => 'Отклонено 1С'],
        ['language' => 'ru', 'name' => 'status_sent_after_approved', 'order' => 0, 'default_value' => 'Подано после одобрения'],
        ['language' => 'ru', 'name' => 'status_sent_after_not_approved', 'order' => 0, 'default_value' => 'Подано после отклонения'],
        ['language' => 'ru', 'name' => 'status_wants_to_return_all', 'order' => 0, 'default_value' => 'Отозвано поступающим'],
        ['language' => 'ru', 'name' => 'draft_status_application_preparing', 'order' => 0, 'default_value' => 'Готовится'],
        ['language' => 'ru', 'name' => 'draft_status_application_sent', 'order' => 0, 'default_value' => 'Подано'],
        ['language' => 'ru', 'name' => 'draft_status_application_moderating', 'order' => 0, 'default_value' => 'На проверке'],
        ['language' => 'ru', 'name' => 'draft_status_application_clean_copy', 'order' => 0, 'default_value' => 'Чистовик'],
        ['language' => 'ru', 'name' => 'draft_status_questionary_preparing', 'order' => 0, 'default_value' => 'Готовится'],
        ['language' => 'ru', 'name' => 'draft_status_questionary_sent', 'order' => 0, 'default_value' => 'Подано'],
        ['language' => 'ru', 'name' => 'draft_status_questionary_moderating', 'order' => 0, 'default_value' => 'На проверке'],
        ['language' => 'ru', 'name' => 'draft_status_questionary_clean_copy', 'order' => 0, 'default_value' => 'Чистовик'],
        ['language' => 'ru', 'name' => 'sending_error_because_of_moderating_now', 'order' => 0, 'default_value' => 'Невозможно подать заявление, так как предыдущее поданное заявление сейчас проверяется модератором'],
        ['language' => 'ru', 'name' => 'can_send_app_message_if_first_try', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Подать заявление\"'],
        ['language' => 'ru', 'name' => 'can_send_app_message_if_second_attempt', 'order' => 0, 'default_value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку \"Обновить заявление\"'],
        ['language' => 'ru', 'name' => 'tooltip_for_view_accepted_statement', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'tooltip_for_make_a_draft_from_the_accepted_statement', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'tooltip_for_view_an_inspection_application', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'tooltip_for_make_a_draft_from_the_previously_sent_statement', 'order' => 0, 'default_value' => ''],
        ['language' => 'ru', 'name' => 'info_message_in_questionary_for_birth_place', 'order' => 0, 'default_value' => 'Заполнять согласно документу, удостоверяющему личность'],
        ['language' => 'ru', 'name' => 'info_message_in_questionary_for_passport_form', 'order' => 0, 'default_value' => 'Заполнять согласно документу, удостоверяющему личность'],
        ['language' => 'ru', 'name' => 'alert_message_in_education_for_form', 'order' => 0, 'default_value' => 'Заполнять строго по документу об образовании'],
        ['language' => 'ru', 'name' => 'info_message_in_education_for_series', 'order' => 0, 'default_value' => 'Серия заполняется только для документов выданный до 2012 года'],
        ['language' => 'ru', 'name' => 'tooltip_for_bachelor_speciality_marked_as_enlisted', 'order' => 0, 'default_value' => 'Вы зачислены по данному направлению подготовки'],
        ['language' => 'ru', 'name' => 'tooltip_for_education_related_with_bachelor_speciality_marked_as_enlisted', 'order' => 0, 'default_value' => 'Вы не можете вносить правки в документ об образовании, так как он связан с зачисленным, в приёмной компании, направлением подготовки.'],
        ['language' => 'ru', 'name' => 'tooltip_for_target_reception_related_with_bachelor_speciality_marked_as_enlisted', 'order' => 0, 'default_value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.'],
        ['language' => 'ru', 'name' => 'tooltip_for_benefits_related_with_bachelor_speciality_marked_as_enlisted', 'order' => 0, 'default_value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.'],
        ['language' => 'ru', 'name' => 'tooltip_for_olympiad_related_with_bachelor_speciality_marked_as_enlisted', 'order' => 0, 'default_value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.'],
        ['language' => 'ru', 'name' => 'text_for_an_empty_line_when_it_was_not_possible_to_collect_a_set_of_entrance_tests', 'order' => 0, 'default_value' => 'Не удалось собрать набор вступительных испытаний'],
    ];

    


    public function safeUp()
    {
        Yii::$app->db->schema->refresh();
        $table = Yii::$app->db->schema->getTableSchema('{{%text_settings}}');
        if (!isset($table->columns['default_value'])) {
            $this->addColumn('{{%text_settings}}', 'default_value', $this->string(3000));
            Yii::$app->db->schema->refresh();
        }
            
        $map = [];
        foreach (static::$default_values as $value) {
            $map[$this->getDefaultValueHash($value)] = $value['default_value'];
        }
        
        $models = TextSetting::find()->andWhere(['application_type' => TextSetting::APPLICATION_TYPE_DEFAULT])->all();
        foreach ($models as $model) {
            $hash = $this->getDefaultValueHash($model);
            $model->default_value = $map[$hash] ?? null;
            if (!$model->save(true, ['default_value'])) {
                Yii::error("Не удалось сохранить значение текста по умолчанию " 
                    . PHP_EOL 
                    . VarDumper::dumpAsString($model->errors), 
                    "TEXT_SETTINGS");
            }
        }
    }

    


    public function safeDown()
    {
        Yii::$app->db->schema->refresh();
        $table = Yii::$app->db->schema->getTableSchema('{{%text_settings}}');
        if (isset($table->columns['default_value'])) {
            $this->dropColumn('{{%text_settings}}', 'default_value');
        }
    }

    



    private static function getDefaultValueHash($model): string
    {
        return crc32("{$model['language']}{$model['name']}{$model['order']}");
    }
}
