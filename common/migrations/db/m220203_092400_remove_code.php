<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m220203_092400_remove_code extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $c = CodeSetting::find()->where(['name' => 'return_agreed_application_to_sent'])->one();
        if ($c) {
            $c->delete();
        }
    }

    


    public function safeDown()
    {
        $code = new CodeSetting();
        $code->description = 'Разрешать возврат к модерации принятых заявлений';
        $code->name = 'return_agreed_application_to_sent';
        $code->value = 0;

        $code->save();
    }
}
