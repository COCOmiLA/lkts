<?php

use common\models\settings\CodeSetting;
use yii\db\Migration;




class m211004_100043_add_block_questionary_setting extends Migration
{
    


    public function safeUp()
    {
        $setting = CodeSetting::find()->where(['name' => 'block_questionary_after_approve'])->one();
        if (!$setting) {
            $setting = new CodeSetting();
            $setting->value = 0;
            $setting->name = 'block_questionary_after_approve';
        }
        $setting->description = 'Блокировать редактирование анкеты после одобрения.';
        $setting->save();
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne([
            'name' => 'block_questionary_after_approve'
        ]);
        if ($setting) {
            $setting->delete();
        }
    }


}
