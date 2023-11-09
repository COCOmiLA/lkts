<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210203_081331_add_code_for_xml_dumping extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Разрешить сохранение заявлений в файл',
            'name' => 'allow_dump_full_package_to_file',
            'value' => '0'
        ];
        $code->save();
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'allow_dump_full_package_to_file',
        ]);
        if (!is_null($code)) {
            $code->delete();
        }
    }

}
