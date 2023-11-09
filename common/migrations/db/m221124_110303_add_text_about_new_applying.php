<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m221124_110303_add_text_about_new_applying extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = new TextSetting();
        $text->attributes = [
            'name' => 'new_application_apply_notification_title',
            'description' => 'Текст заголовка оповещения модератора при подаче нового заявления в приёмную кампанию',
            'value' => 'Подано новое заявление в приёмную кампанию {campaignName}',
            'category' => TextSetting::CATEGORY_NOTIFICATIONS
        ];
        $text->save(false);

        $text = new TextSetting();
        $text->attributes = [
            'name' => 'new_application_apply_notification_body',
            'description' => 'Текст тела оповещения модератора при подаче нового заявления в приёмную кампанию',
            'value' => 'В приёмную кампанию {campaignName} подано новое заявление от {applicantName} ({applicantEmail})',
            'category' => TextSetting::CATEGORY_NOTIFICATIONS
        ];
        $text->save(false);
    }

    


    public function safeDown()
    {
        $settings_to_delete = [
            [
                'name' => 'new_application_apply_notification_title',
                'category' => TextSetting::CATEGORY_NOTIFICATIONS
            ],
            [
                'name' => 'new_application_apply_notification_body',
                'category' => TextSetting::CATEGORY_NOTIFICATIONS
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
