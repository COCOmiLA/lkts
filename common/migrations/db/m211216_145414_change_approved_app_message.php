<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m211216_145414_change_approved_app_message extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = TextSetting::find()
            ->where([
                'name' => 'application_approved_sandbox_on',
                'language' => 'ru'
            ])
            ->one();
        $text->value = 'Заявление одобрено модератором и отправлено на рассмотрение в образовательную организацию';
        $text->save(false);

        $text = TextSetting::find()
            ->where([
                'name' => 'application_approved_sandbox_off',
                'language' => 'ru'
            ])
            ->one();
        $text->value = 'Заявление отправлено на рассмотрение в образовательную организацию';
        $text->save(false);
    }
}
