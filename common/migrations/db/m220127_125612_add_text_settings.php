<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220127_125612_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_created',
                'description' => 'Текст статуса заявления "Не подано"',
                'value' => 'Не подано',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_sent',
                'description' => 'Текст статуса заявления "Подано впервые"',
                'value' => 'Подано впервые',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_approved',
                'description' => 'Текст статуса заявления "Принято"',
                'value' => 'Принято',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_not_approved',
                'description' => 'Текст статуса заявления "Отклонено"',
                'value' => 'Отклонено',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_rejected_by1_c',
                'description' => 'Текст статуса заявления "Ошибка системы при одобрении"',
                'value' => 'Ошибка системы при одобрении',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_sent_after_approved',
                'description' => 'Текст статуса заявления "Подано после одобрения"',
                'value' => 'Подано после одобрения',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_sent_after_not_approved',
                'description' => 'Текст статуса заявления "Подано после отклонения"',
                'value' => 'Подано после отклонения',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'status_wants_to_return_all',
                'description' => 'Текст статуса заявления "Отозвано поступающим"',
                'value' => 'Отозвано поступающим',
                'category' => 4

            ]
        );

        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_application_preparing',
                'description' => 'Текст статуса черновика заявления "Готовится"',
                'value' => 'Готовится',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_application_sent',
                'description' => 'Текст статуса черновика заявления "Подано"',
                'value' => 'Подано',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_application_moderating',
                'description' => 'Текст статуса черновика заявления "На проверке"',
                'value' => 'На проверке',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_application_clean_copy',
                'description' => 'Текст статуса черновика заявления "Чистовик"',
                'value' => 'Чистовик',
                'category' => 4

            ]
        );

        
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_questionary_preparing',
                'description' => 'Текст статуса черновика заявления "Готовится"',
                'value' => 'Готовится',
                'category' => 2

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_questionary_sent',
                'description' => 'Текст статуса черновика заявления "Подано"',
                'value' => 'Подано',
                'category' => 2

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_questionary_moderating',
                'description' => 'Текст статуса черновика заявления "На проверке"',
                'value' => 'На проверке',
                'category' => 2

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'draft_status_questionary_clean_copy',
                'description' => 'Текст статуса черновика заявления "Чистовик"',
                'value' => 'Чистовик',
                'category' => 2

            ]
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'status_sent',
                'status_created',
                'status_approved',
                'status_not_approved',
                'status_rejected_by1_c',
                'status_sent_after_approved',
                'status_wants_to_return_all',
                'status_sent_after_not_approved',
                'draft_status_application_sent',
                'draft_status_questionary_sent',
                'draft_status_application_preparing',
                'draft_status_questionary_preparing',
                'draft_status_application_clean_copy',
                'draft_status_questionary_clean_copy',
                'draft_status_application_moderating',
                'draft_status_questionary_moderating',
            ]]
        );
    }
}
