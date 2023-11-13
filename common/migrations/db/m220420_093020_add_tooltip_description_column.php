<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220420_093020_add_tooltip_description_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%text_settings}}', 'tooltip_description', $this->string(1000));
        \common\models\settings\TextSetting::updateAll(
            ['description' => 'Текст статуса заявления "Ошибка системы при одобрении"'],
            ['description' => 'Текст статуса заявления "Отклонено 1С"']
        );
        \common\models\settings\TextSetting::updateAll(
            ['description' => 'Текст статуса заявления "Отозвано поступающим"'],
            ['description' => 'Текст статуса заявления "Отозвано"']
        );
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%text_settings}}', 'tooltip_description');
    }
}
