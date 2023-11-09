<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220525_090051_rename_texts extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        \common\models\settings\TextSetting::updateAll([
            'description' => 'Текст сообщения, напоминающего о необходимости нажать "Подать заявление"',
        ],
            [
                'name' => 'can_send_app_message_if_first_try',
            ]);

        \common\models\settings\TextSetting::updateAll([
            'description' => 'Текст сообщения, напоминающего о необходимости нажать "Обновить заявление"',
        ],
            [
                'name' => 'can_send_app_message_if_second_attempt',
            ]);
    }
}
