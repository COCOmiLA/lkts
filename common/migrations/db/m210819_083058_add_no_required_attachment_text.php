<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210819_083058_add_no_required_attachment_text extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = new TextSetting();
        $text->attributes = [
            'name' => 'no_required_attachment_text',
            'description' => 'Текст сообщения об отсутствии обязательного файла',
            'value' => 'Отсутствует обязательный для прикрепления файл.',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ];
        $text->save(false);
    }

    


    public function safeDown()
    {
        $to_delete = TextSetting::findOne([
            'name' => 'no_required_attachment_text',
            'category' => TextSetting::CATEGORY_ALL
        ]);
        if ($to_delete) {
            $to_delete->delete();
        }
    }
}
