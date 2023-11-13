<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210916_054220_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            TextSetting::tableName(),
            [
                'name' => 'global_text_for_submit_tooltip',
                'description' => 'Текст сообщения для всех `submit`-кнопок',
                'value' => 'Запрос обрабатывается...',
                'order' => 0,
                'category' => TextSetting::CATEGORY_ALL,

            ]
        );
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(
            TextSetting::tableName(),
            ['name' => 'global_text_for_submit_tooltip']
        );
    }
}
