<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220301_123029_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_view_accepted_statement',
                'description' => 'Текст подсказки для ссылки на принятое ранее заявление; на панели навигации ЛК',
                'value' => '',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_make_a_draft_from_the_accepted_statement',
                'description' => 'Текст подсказки для кнопки открытия модального окна создания черновика из принятого заявления; на панели навигации ЛК',
                'value' => '',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_view_an_inspection_application',
                'description' => 'Текст подсказки для ссылки на заявление находящиеся на проверке; на панели навигации ЛК',
                'value' => '',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_make_a_draft_from_the_previously_sent_statement',
                'description' => 'Текст подсказки для кнопки открытия модального окна создания черновика из ранее отправленного заявления; на панели навигации ЛК',
                'value' => '',
                'category' => 12

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'view_accepted_statement',
                'view_an_inspection_application',
                'make_a_draft_from_the_accepted_statement',
                'make_a_draft_from_the_previously_sent_statement',
            ]]
        );
    }
}
