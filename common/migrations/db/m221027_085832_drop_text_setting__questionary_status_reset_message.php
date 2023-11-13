<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221027_085832_drop_text_setting__questionary_status_reset_message extends MigrationWithDefaultOptions
{
    private const TN = '{{%text_settings}}';

    


    public function safeUp()
    {
        $this->delete(
            self::TN,
            ['name' => 'questionary_status_reset_message']
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->insert(
            self::TN,
            [
                'name' => 'questionary_status_reset_message',
                'description' => 'Текст предупреждения о сбросе статуса при изменении данных анкеты',
                'value' => 'При изменении данных анкеты все ваши заявления получат статус "Не подано"',
                'category' => 2,
                'language' => 'ru',

            ]
        );

        Yii::$app->db->schema->refresh();
    }
}
