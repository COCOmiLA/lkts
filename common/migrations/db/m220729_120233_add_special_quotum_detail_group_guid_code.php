<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m220729_120233_add_special_quotum_detail_group_guid_code extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description = 'Код специальной квоты из справочника "ОсобенностиПриема"';
        $code->name = 'special_quota_detail_group_guid';
        $code->value = '';
        $code->save(false);
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'special_quota_detail_group_guid',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
