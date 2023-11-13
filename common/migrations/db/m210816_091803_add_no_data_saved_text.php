<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m210816_091803_add_no_data_saved_text extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = new TextSetting();
        $setting->attributes =  [
            'name' => 'no_data_saved_text',
            'description' => 'Текст показываемый поступающему если он сохранил данные не внеся изменений',
            'value' => 'Изменения в данных не были обнаружены. Ничего не было сохранено.',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ];
        $setting->save(false);
    }

    


    public function safeDown()
    {
        $to_delete = TextSetting::findOne([
            'name' => 'no_data_saved_text',
            'category' => TextSetting::CATEGORY_ALL
        ]);
        if ($to_delete != null) {
            $to_delete->delete();
        }
    }

}
