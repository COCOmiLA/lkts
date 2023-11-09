<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220203_123148_add_text extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = new TextSetting();
        $text->attributes = [
            'name' => 'sending_error_because_of_moderating_now',
            'description' => 'Текст о невозможности подачи заявления, так как предыдущее поданное сейчас проверяется модератором',
            'value' => 'Невозможно подать заявление, так как предыдущее поданное заявление сейчас проверяется модератором',
            'order' => 0,
            'category' => TextSetting::CATEGORY_APPLICATION
        ];
        $text->save(false);
    }

    


    public function safeDown()
    {
        $settings_to_delete = [
            [
                'name' => 'sending_error_because_of_moderating_now',
                'category' => TextSetting::CATEGORY_APPLICATION
            ],
        ];
        foreach ($settings_to_delete as $setting) {
            $to_delete = TextSetting::findOne($setting);
            if ($to_delete != null) {
                $to_delete->delete();
            }
        }
    }
}
