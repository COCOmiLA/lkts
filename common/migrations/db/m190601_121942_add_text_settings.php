<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m190601_121942_add_text_settings extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'questionary__create_from_1C',
            'description' => 'Сообщение абитуриенту о том, что анкета была создана из 1С',
            'value' => 'Анкета была обновлена из ПК. Требуется проверить правильность внесённых данных.',
        ]);
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%text_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
