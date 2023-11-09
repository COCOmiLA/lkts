<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200723_135514_insert_sort_code_to_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('code_settings', [
            'name' => 'scan_sort_code',
            'description' => 'Код сортировки скан-копий',
            'value'=>'0'
        ]);
    }
    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'scan_sort_code',
        ]);
        if($code != null) {
            $code->delete();
        }
    }

}
