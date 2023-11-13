<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220225_074149_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'info_message_in_questionary_for_birth_place',
                'description' => 'Текст сообщения информирующего об форме заполнения поля "Место рождения" на форме анкеты',
                'value' => 'Заполнять согласно документу, удостоверяющему личность',
                'category' => 2

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'info_message_in_questionary_for_passport_form',
                'description' => 'Текст сообщения информирующего об форме заполнения формы "паспортные данные" на странице анкеты',
                'value' => 'Заполнять согласно документу, удостоверяющему личность',
                'category' => 2

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'info_message_in_questionary_for_birth_place',
                'info_message_in_questionary_for_passport_form',
            ]]
        );
    }
}
