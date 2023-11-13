<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220926_142026_add_text_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_in_popup_autofill_specialty_on_a_universal_basis',
                'description' => 'Текст в модальном окне автоматического добавления конкурсной группы',
                'value' => 'Хотите добавить направление подготовки для приёма на общих основаниях? В список направлений подготовки будет добавлено',
                'category' => TextSetting::CATEGORY_APPLICATION,
                'tooltip_description' => 'Текст появляется в окне автоматического добавления конкурсной группы, если поступающей не выбрал направления на общих основаниях',
                'default_value' => 'Хотите добавить направление подготовки для приёма на общих основаниях? В список направлений подготовки будет добавлено',
            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', ['name' => 'text_in_popup_autofill_specialty_on_a_universal_basis']);
    }
}
