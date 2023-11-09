<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\db\Query;




class m221209_103409_add_new_parent_data_settings_if_not_exists extends MigrationWithDefaultOptions
{
    protected static $codes = [
        [
            'name' => 'require_parent_passport_data',
            'value' => '1',
            'description' => 'Требовать заполнение паспорта у родителей'
        ],
        [
            'name' => 'require_parent_address_data',
            'value' => '1',
            'description' => 'Требовать заполнение адреса у родителей'
        ],
        [
            'name' => 'hide_parent_passport_data_in_list',
            'value' => '0',
            'description' => 'Скрыть отображение паспортных данных родителей в списке'
        ],
        [
            'name' => 'hide_passport_data_block',
            'value' => '0',
            'description' => 'Скрывать поля ввода паспортных данных родителей или законных представителей'
        ],
        [
            'name' => 'hide_address_data_block',
            'value' => '0',
            'description' => 'Скрывать поля ввода адреса родителей или законных представителей'
        ],
    ];

    


    public function safeUp()
    {
        foreach (self::$codes as $code) {
            $exists  = (new Query())->from('{{%parent_data_settings}}')->where(['name' => $code['name']])->exists();
            if (!$exists) {
                $this->insert('{{%parent_data_settings}}', [
                    'name' => $code['name'],
                    'value' => $code['value'],
                    'description' => $code['description']
                ]);
            }
        }
    }

    


    public function safeDown()
    {
        foreach (self::$codes as $code) {
            $this->delete('{{%parent_data_settings}}', ['name' => $code['name']]);
        }
    }
}
