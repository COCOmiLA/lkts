<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m160525_134623_add_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = CodeSetting::findOne([
            'name' => 'category_olympiad'
        ]);
        if (empty($code)) {
            $this->insert(
                '{{%code_settings}}',
                [
                    'value' => '000000001',
                    'name' => 'category_olympiad',
                    'description' => 'Код категории приема без вступительных испытаний',
                ]
            );
        }
        $code = CodeSetting::findOne([
            'name' => 'category_specific_law'
        ]);
        if (empty($code)) {
            $this->insert(
                '{{%code_settings}}',
                [
                    'value' => '000000002',
                    'name' => 'category_specific_law',
                    'description' => 'Код категории приема абитуриентов имеющих особое право',
                ]
            );
        }
        $code = CodeSetting::findOne([
            'name' => 'category_all'
        ]);
        if (empty($code)) {
            $this->insert(
                '{{%code_settings}}',
                [
                    'value' => '000000003',
                    'name' => 'category_all',
                    'description' => 'Код категории приема на общих основаниях',
                ]
            );
        }
    }

    


    public function safeDown()
    {
        echo "m140501_075310_add_code_settings cannot be reverted.\n";
    }
}
