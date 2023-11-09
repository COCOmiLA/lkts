<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230124_092336_change_text_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->update(
            self::TN,
            ['default_value' => 'Уже подали заявление? Получите пароль от личного кабинета.'],
            ['name' => 'createacc_link_text']
        );

        $this->update(
            self::TN,
            ['value' => 'Уже подали заявление? Получите пароль от личного кабинета.'],
            ['value' => 'Уже подали заявление? Получите пароль от личного кабинета'],
        );
    }

    


    public function safeDown()
    {
        $this->update(
            self::TN,
            ['default_value' => 'Уже подали заявление? Получите пароль от личного кабинета'],
            ['name' => 'createacc_link_text']
        );

        $this->update(
            self::TN,
            ['value' => 'Уже подали заявление? Получите пароль от личного кабинета'],
            ['value' => 'Уже подали заявление? Получите пароль от личного кабинета.'],
        );
    }
}
