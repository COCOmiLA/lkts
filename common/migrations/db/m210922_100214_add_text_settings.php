<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210922_100214_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            TextSetting::tableName(),
            [
                'name' => 'questionary_status_reset_message',
                'description' => 'Текст предупреждения о сбросе статуса при изменении данных анкеты',
                'value' => 'При изменении данных анкеты все ваши заявления получат статус "Не подано"',
                'order' => 0,
                'category' => TextSetting::CATEGORY_QUESTIONARY

            ]
        );
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(
            TextSetting::tableName(),
            ['name' => 'questionary_status_reset_message']
        );
    }
}
