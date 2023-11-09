<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230117_132735_add_text_setting extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->insert(
            self::TN,
            [
                'name' => 'text_for_original_epgu_on_education_data',
                'value' => 'Предоставлен оригинал документа установленного образца на ЕПГУ.',
                'category' => 'education',
                'description' => 'Текст сообщающий о наличии оригинала в ЕПГУ',
                'default_value' => 'Предоставлен оригинал документа установленного образца на ЕПГУ.',
                'tooltip_description' => 'Отображается в модальном окне формы документа об образовании в виде информационного сообщения',
            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(self::TN, ['name' => 'text_for_original_epgu_on_education_data']);
    }
}
