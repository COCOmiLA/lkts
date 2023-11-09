<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160419_123157_add_new_text_settings extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'info_agreement',
            'description' => 'Сообщение пользователю о порядке прикрепления согласия на зачисление',
            'value' => 'Для прикрепления согласия на зачисление необходимо скачать бланк документа, распечатать его, заполнить, подписать, отсканировать и прикрепить обратно.',
        ]);

        $this->insert('{{%text_settings}}', [
            'name' => 'need_agreement',
            'description' => 'Сообщение пользователю об ошибке, связанной с отстутсвием прикрепленного согласия на зачисление',
            'value' => 'Необходимо прикрепить согласие на зачисление.',
        ]);

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => ['info_agreement', 'need_agreement']
        ]);
        Yii::$app->db->schema->refresh();
    }
}
