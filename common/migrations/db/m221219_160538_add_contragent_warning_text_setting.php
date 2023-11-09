<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221219_160538_add_contragent_warning_text_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $exists = (new Query())->from('{{%text_settings}}')->where([
            'name' => 'pending_contragent_message'
        ])->exists();

        if ($exists) {
            \Yii::error('Текст pending_contragent_message уже существует', 'TEXT_SETTINGS');
            return true;
        }

        $value = 'Содержатся неподтверждённые записи о контрагентах';

        $this->insert('{{%text_settings}}', [
            'name' => 'pending_contragent_message',
            'description' => 'Текст сообщения о том, что в заявлении содержатся неподтверждённые записи о контрагентах',
            'value' => $value,
            'category' => 'sandbox',
            'application_type' => 0,
            'language' => 'ru',
            'default_value' => $value
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%text_settings}}', [
            'name' => 'pending_contragent_message', 
        ]);
    }
}
