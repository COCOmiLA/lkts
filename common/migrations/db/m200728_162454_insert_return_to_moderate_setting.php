<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200728_162454_insert_return_to_moderate_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description = 'Разрешать возврат к модерации принятых заявлений';
        $code->name = 'return_agreed_application_to_sent';
        $code->value = 0;

        $code->save();
    }

    


    public function safeDown()
    {
        return;
    }

    













}
