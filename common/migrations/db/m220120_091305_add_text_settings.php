<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220120_091305_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_for_set_when_speciality-bvi',
                'description' => 'Текст для пустой строки набора ВИ, когда НП БВИ',
                'value' => 'Вступительные испытания не требуются',
                'category' => TextSetting::CATEGORY_EXAMS

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_for_sets_when_failed_to_collect_with_completed_profile',
                'description' => 'Текст для пустой строки набора ВИ, когда не удалось собрать с заполненным профилем',
                'value' => 'Не удалось собрать набор вступительных испытаний по указанным профилям образования',
                'category' => TextSetting::CATEGORY_EXAMS

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'text_for_sets_when_failed_to_collect_with_a_not_filled_profile',
                'description' => 'Текст для пустой строки набора ВИ, когда не удалось собрать с не заполненным профилем',
                'value' => 'Не удалось собрать набор вступительных испытаний',
                'category' => TextSetting::CATEGORY_EXAMS

            ]
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'text_for_set_when_speciality',
                'text_for_sets_when_failed_to_collect_with_completed_profile',
                'text_for_sets_when_failed_to_collect_with_a_not_filled_profile',
            ]]
        );
    }
}
