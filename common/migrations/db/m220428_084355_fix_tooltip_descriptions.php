<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220428_084355_fix_tooltip_descriptions extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text_batches = \common\models\settings\TextSetting::find()->batch(50);
        foreach ($text_batches as $batch) {
            foreach ($batch as $text) {
                $text->tooltip_description = trim((string)$text->tooltip_description, '"');
                $text->save(false);
            }
        }
    }
}
