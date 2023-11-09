<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220225_083857_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'alert_message_in_education_for_form',
                'description' => 'Текст сообщения информирующего об форме заполнения формы "Документ об образовании" на странице образования',
                'value' => 'Заполнять строго по документу об образовании',
                'category' => 5
            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'info_message_in_education_for_series',
                'description' => 'Текст сообщения информирующего об условии заполнения поля "серия" в форме "Документ об образовании"',
                'value' => 'Серия заполняется только для документов выданным до 2012 года',
                'category' => 5
            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'alert_message_in_education_for_form',
                'info_message_in_education_for_series',
            ]]
        );
    }
}
