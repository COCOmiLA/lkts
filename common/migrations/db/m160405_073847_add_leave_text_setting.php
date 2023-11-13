<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160405_073847_add_leave_text_setting extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'unsaved_leave',
            'description' => 'Сообщение пользователю при уходе со страницы, что есть несохраненные изменения',
            'value' => 'На странице есть несохраненные изменения. Если вы покинете страницу без сохранения, они будут потеряны.',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => ['unsaved_leave']
        ]);
        Yii::$app->db->schema->refresh();
    }
}
