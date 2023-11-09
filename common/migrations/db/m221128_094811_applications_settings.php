<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221128_094811_applications_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%applications_settings}}', [
            'id' => $this->primaryKey(),

            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string()->notNull(),
        ]);

        $this->insert('{{%applications_settings}}', [
            'name' => 'move_step_forward_on_form_submit',
            'description' => 'Перенаправлять на следующую страницу заявления после заполнения данных (кнопка Далее)',
            'type' => 'checkbox',
            'value' => '0',
        ]);

    }

    


    public function safeDown()
    {
        $this->dropTable('{{%applications_settings}}');
    }
}
