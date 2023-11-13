<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220204_061826_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'can_send_app_message_if_first_try',
                'description' => 'Текст сообщения напоминающего об необходимости нажать "Подать заявление"',
                'value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Подать заявление"',
                'category' => 4

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'can_send_app_message_if_second_attempt',
                'description' => 'Текст сообщения напоминающего об необходимости нажать "Обновить заявление"',
                'value' => '<strong>Внимание!</strong> Для подачи заявления в приёмную кампанию необходимо нажать на кнопку "Обновить заявление"',
                'category' => 4

            ]
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'can_send_app_message_if_first_try',
                'can_send_app_message_if_second_attempt',
            ]]
        );

        Yii::$app->db->schema->refresh();
    }
}
