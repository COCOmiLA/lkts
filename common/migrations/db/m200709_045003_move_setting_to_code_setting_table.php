<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200709_045003_move_setting_to_code_setting_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'can_change_fio_after_first_application',
            'value' => '1',
            'description' => 'Сможет ли поступающий после первой подачи заявления менять ФИО и паспортные данные.',
        ]);
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'can_change_fio_after_first_application',
        ]);
        if ($code != null) {
            $code->delete();
        }

    }

    













}
