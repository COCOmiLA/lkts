<?php

use backend\models\MasterSystemManagerInterfaceSetting;
use common\components\Migration\MigrationWithDefaultOptions;




class m210412_140739_add_new_master_system_manager_intefase_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $newSetting = new MasterSystemManagerInterfaceSetting();
        $newSetting->name = 'use_master_system_manager_interface';
        $newSetting->value = '0';
        $newSetting->type = 'bool';
        if($newSetting->validate()) {
            return $newSetting->save(false);
        }
        Yii::error('Невозможно создать системную настройку для интерфейса модератора в 1С. (use_master_system_manager_interface)' . "\n" . print_r($newSetting->errors, true), 'MIGRATION_MASTER_SYSTEM_SETTING');
        return false;
    }

    


    public function safeDown()
    {
        $setting = MasterSystemManagerInterfaceSetting::findOne([
            'name' => 'use_master_system_manager_interface'
        ]);
        if($setting !== null) {
            $setting->delete();
        }
        return true;
    }

    













}
