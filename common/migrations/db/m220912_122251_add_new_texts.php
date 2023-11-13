<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220912_122251_add_new_texts extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $benefitTarget = new TextSetting();
        $benefitTarget->attributes = [
            'name' => 'need_update_app_from_one_s',
            'description' => 'Текст, уведомляющий о наличии в 1С:Университет ПРОФ заявления поступающего с более поздней датой изменения',
            'value' => 'Заявление устарело. В 1С:Университет ПРОФ присутствует заявление с более поздней датой. Необходимо отклонить заявление для использования поступающим актуальных данных.',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ];
        $benefitTarget->save(false);

        $benefitTarget = new TextSetting();
        $benefitTarget->attributes = [
            'name' => 'need_update_questionary_from_one_s',
            'description' => 'Текст, уведомляющий о наличии в 1С:Университет ПРОФ анкеты поступающего с более поздней датой изменения',
            'value' => 'Заявление устарело. Различаются версии данных анкеты поступающего в Личном кабинете и 1С:Университет ПРОФ, поступающему необходимо актуализировать данные анкеты перед подачей.',
            'order' => 0,
            'category' => TextSetting::CATEGORY_ALL
        ];
        $benefitTarget->save(false);
    }

    


    public function safeDown()
    {
        $this->delete(TextSetting::tableName(), ['name' => 'need_update_app_from_one_s']);
        $this->delete(TextSetting::tableName(), ['name' => 'need_update_questionary_from_one_s']);
    }
}
