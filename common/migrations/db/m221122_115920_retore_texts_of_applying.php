<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m221122_115920_retore_texts_of_applying extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'can_send_app_message_if_first_try',
                'can_send_app_message_if_second_attempt',
            ]]
        );

        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'can_send_app_message',
                'description' => 'Текст сообщения напоминающего о необходимости нажать "Отправить в приемную комиссию"',
                'value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Отправить в приемную комиссию"',
                'category' => TextSetting::CATEGORY_APPLICATION
            ]
        );

    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'can_send_app_message',
            ]]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'can_send_app_message_if_first_try',
                'description' => 'Текст сообщения напоминающего об необходимости нажать "Подать заявление"',
                'value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Подать заявление"',
                'category' => TextSetting::CATEGORY_APPLICATION

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'can_send_app_message_if_second_attempt',
                'description' => 'Текст сообщения напоминающего об необходимости нажать "Обновить заявление"',
                'value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Обновить заявление"',
                'category' => TextSetting::CATEGORY_APPLICATION

            ]
        );
    }
}
