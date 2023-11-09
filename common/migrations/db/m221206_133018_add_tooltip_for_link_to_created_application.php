<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221206_133018_add_tooltip_for_link_to_created_application extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_link_to_created_application',
                'description' => 'Текст подсказки для ссылки на заявление готовящееся к подаче; на панели навигации ЛК',
                'value' => '',
                'category' => \common\models\settings\TextSetting::CATEGORY_TOOLTIPS

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', ['name' => 'tooltip_for_link_to_created_application']);
    }
}
