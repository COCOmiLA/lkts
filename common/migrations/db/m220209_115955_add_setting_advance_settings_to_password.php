<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220209_115955_add_setting_advance_settings_to_password extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%auth_settings}}',
            [
                'value' => 6,
                'name' => 'minimal_password_length',
            ]
        );
        $this->insert(
            '{{%auth_settings}}',
            [
                'value' => 0,
                'name' => 'password_must_contain_capital_letters',
            ]
        );
        $this->insert(
            '{{%auth_settings}}',
            [
                'value' => 0,
                'name' => 'password_must_contain_numbers',
            ]
        );
        $this->insert(
            '{{%auth_settings}}',
            [
                'value' => 0,
                'name' => 'password_must_contain_special_characters',
            ]
        );

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->delete(
            '{{%auth_settings}}',
            ['name' => [
                'minimal_password_length',
                'password_must_contain_numbers',
                'password_must_contain_capital_letters',
                'password_must_contain_special_characters',
            ]]
        );

        Yii::$app->db->schema->refresh();
    }
}
