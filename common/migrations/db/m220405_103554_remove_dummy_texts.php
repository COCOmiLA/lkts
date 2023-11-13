<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220405_103554_remove_dummy_texts extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        TextSetting::updateAll(
            [
                'value' => '',
            ],
            [
                'value' => 'dummy text',
            ]
        );
    }

}
