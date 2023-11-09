<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210913_114321_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            TextSetting::tableName(),
            [
                'name' => 'global_text_for_ajax_tooltip',
                'description' => 'Текст сообщения для всех `aJax`-кнопок',
                'value' => 'Запрос обрабатывается...',
                'order' => 0,
                'category' => TextSetting::CATEGORY_ALL,

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            TextSetting::tableName(),
            ['name' => 'global_text_for_ajax_tooltip']
        );
    }
}
