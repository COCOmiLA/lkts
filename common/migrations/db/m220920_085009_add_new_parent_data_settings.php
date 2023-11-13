<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\ParentDataSetting;
use yii\helpers\VarDumper;




class m220920_085009_add_new_parent_data_settings extends MigrationWithDefaultOptions
{
    protected static $codes = [
        [
            'name' => 'hide_passport_data_block',
            'value' => '0',
            'description' => 'Скрывать поля ввода паспортных данных родителей или законных представителей'
        ],
        [
            'name' => 'hide_address_data_block',
            'value' => '0',
            'description' => 'Скрывать поля ввода адреса родителей или законных представителей'
        ]
    ];
    
    


    public function safeUp()
    {
        foreach (static::$codes as $code) {
            $model = ParentDataSetting::findOne(['name' => $code['name']]);
            if ($model === null) {
                $model = new ParentDataSetting();
            }

            $model->setAttributes($code);
            if (!$model->save(true, ['name', 'description', 'value'])) {
                Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($model->errors), 'PARENT_DATA_SETTINGS');
            }
        }
    }

    


    public function safeDown()
    {
        foreach (static::$codes as $code) {
            $model = ParentDataSetting::findOne(['name' => $code['name']]);
            if ($model) {
                $model->delete();
            }
        }
    }
}
