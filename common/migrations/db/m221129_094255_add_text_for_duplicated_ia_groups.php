<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m221129_094255_add_text_for_duplicated_ia_groups extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $text = new TextSetting();
        $text->attributes = [
            'name' => 'several_achievements_with_same_group_chosen',
            'description' => 'Текст, если выбрано несколько индивидуальных достижений из одной группы',
            'value' => 'Выбрано несколько индивидуальных достижений из одной группы. Баллы будут учитываться только за одно индивидуальное достижение из группы.',
            'category' => TextSetting::CATEGORY_INDACH
        ];
        $text->save(false);
    }

    


    public function safeDown()
    {
        TextSetting::deleteAll(['name' => 'several_achievements_with_same_group_chosen']);
    }
}
