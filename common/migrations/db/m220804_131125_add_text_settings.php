<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220804_131125_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_for_passport_form_footer_on_registration_page',
                'description' => 'Текст нижнего колонтитула для формы паспорта на странице регистрации',
                'value' => 'Заполнять строго в соответствии с документом, удостоверяющим личность (без пробелов)',
                'category' => 0,
                'default_value' => 'Заполнять строго в соответствии с документом, удостоверяющим личность (без пробелов)',

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', ['name' => 'text_for_passport_form_footer_on_registration_page']);
    }
}
