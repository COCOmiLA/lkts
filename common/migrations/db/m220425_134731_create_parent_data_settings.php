<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;
use common\models\settings\ParentDataSetting;
use yii\helpers\VarDumper;




class m220425_134731_create_parent_data_settings extends MigrationWithDefaultOptions
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
        $this->downPreviousChanges();
        
        $this->createTable('{{%parent_data_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'value' => $this->string(1000)->notNull(),
            'description' => $this->string(1000)->notNull(),
        ]);
        
        foreach ($this->codes as $code) {
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
        $this->dropTable('{{%parent_data_settings}}');
        $this->upPreviousChanges();
    }
    
    


    protected function downPreviousChanges()
    {
        foreach ($this->codes as $code) {
            $model = CodeSetting::findOne(['name' => $code['name']]);
            if ($model) {
                $model->delete();
            }
        }
    }
    
    protected function upPreviousChanges()
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
}
