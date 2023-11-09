<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;
use yii\helpers\VarDumper;




class m220316_075245_add_parent_passport_code_settings extends MigrationWithDefaultOptions
{
    protected $codes = [
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
        ]
    ];

    


    public function safeUp()
    {
        foreach ($this->codes as $code) {
            $model = CodeSetting::findOne(['name' => $code['name']]);
            if ($model === null) {
                $model = new CodeSetting();
            }

            $model->setAttributes($code);
            if (!$model->save(true, ['name', 'description', 'value'])) {
                Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($model->errors), 'CODE_SETTINGS');
            }
        }
    }

    


    public function safeDown()
    {
        foreach ($this->codes as $code) {
            $model = CodeSetting::findOne(['name' => $code['name']]);
            if ($model) {
                $model->delete();
            }
        }
    }
}
