<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220330_112359_add_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_bachelor_speciality_marked_as_enlisted',
                'description' => 'Текст подсказки для пиктограмки принятого в 1С направления подготовки; в заголовке панели направления подготовки на странице направлений подготовки',
                'value' => 'Вы зачислены по данному направлению подготовки',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_education_related_with_bachelor_speciality_marked_as_enlisted',
                'description' => 'Текст подсказки для пиктограмки в таблице документов об образовании, для образования связанного с зачисленным, в приёмной компании, направлением подготовки; на странице документов об образовании',
                'value' => 'Вы не можете вносить правки в документ об образовании, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_target_reception_related_with_bachelor_speciality_marked_as_enlisted',
                'description' => 'Текст подсказки для пиктограмки в таблице документов об целевом приёме, для целевого договора связанного с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
                'value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_benefits_related_with_bachelor_speciality_marked_as_enlisted',
                'description' => 'Текст подсказки для пиктограмки в таблице документов об преимущественном праве, для преимущественного права связанного с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
                'value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
                'category' => 12

            ]
        );
        $this->insert(
            '{{%text_settings}}',
            [
                'name' => 'tooltip_for_olympiad_related_with_bachelor_speciality_marked_as_enlisted',
                'description' => 'Текст подсказки для пиктограмки в таблице документов об олимпиад, для олимпиады связанной с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
                'value' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
                'category' => 12

            ]
        );
    }

    


    public function safeDown()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => [
                'tooltip_for_bachelor_speciality_marked_as_enlisted',
                'tooltip_for_education_related_with_bachelor_speciality_marked_as_enlisted',
                'tooltip_for_target_reception_related_with_bachelor_speciality_marked_as_enlisted',
                'tooltip_for_benefits_related_with_bachelor_speciality_marked_as_enlisted',
                'tooltip_for_olympiad_related_with_bachelor_speciality_marked_as_enlisted',
            ]]
        );
    }
}
