<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m220824_115942_fix_text_settings_misspells extends MigrationWithDefaultOptions
{
    protected static $settings_to_change = [
        [
            'language' => 'ru',
            'name' => 'tooltip_for_education_related_with_bachelor_speciality_marked_as_enlisted',
            'order' => 0,
            'default_value_old' => 'Вы не можете вносить правки в документ об образовании, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
            'default_value_new' => 'Вы не можете вносить правки в документ об образовании, так как он связан с зачисленным, в приёмной кампании, направлением подготовки.',
            'description_old' => 'Текст подсказки для пиктограмки в таблице документов об образовании, для образования связанного с зачисленным, в приёмной компании, направлением подготовки; на странице документов об образовании',
            'description_new' => 'Текст подсказки для пиктограмки в таблице документов об образовании, для образования связанного с зачисленным, в приёмной кампании, направлением подготовки; на странице документов об образовании'
        ],
        [
            'language' => 'ru',
            'name' => 'tooltip_for_target_reception_related_with_bachelor_speciality_marked_as_enlisted',
            'order' => 0,
            'default_value_old' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
            'default_value_new' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной кампании, направлением подготовки.',
            'description_old' => 'Текст подсказки для пиктограмки в таблице документов об целевом приёме, для целевого договора связанного с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
            'description_new' => 'Текст подсказки для пиктограмки в таблице документов об целевом приёме, для целевого договора связанного с зачисленным, в приёмной кампании, направлением подготовки; на странице льгот и преимущественного права'
        ],
        [
            'language' => 'ru',
            'name' => 'tooltip_for_benefits_related_with_bachelor_speciality_marked_as_enlisted',
            'order' => 0,
            'default_value_old' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
            'default_value_new' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной кампании, направлением подготовки.',
            'description_old' => 'Текст подсказки для пиктограмки в таблице документов об преимущественном праве, для преимущественного права связанного с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
            'description_new' => 'Текст подсказки для пиктограмки в таблице документов об преимущественном праве, для преимущественного права связанного с зачисленным, в приёмной кампании, направлением подготовки; на странице льгот и преимущественного права'
        ],
        [
            'language' => 'ru',
            'name' => 'tooltip_for_olympiad_related_with_bachelor_speciality_marked_as_enlisted',
            'order' => 0,
            'default_value_old' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной компании, направлением подготовки.',
            'default_value_new' => 'Вы не можете вносить правки в документ, так как он связан с зачисленным, в приёмной кампании, направлением подготовки.',
            'description_old' => 'Текст подсказки для пиктограмки в таблице документов об олимпиад, для олимпиады связанной с зачисленным, в приёмной компании, направлением подготовки; на странице льгот и преимущественного права',
            'description_new' => 'Текст подсказки для пиктограмки в таблице документов об олимпиад, для олимпиады связанной с зачисленным, в приёмной кампании, направлением подготовки; на странице льгот и преимущественного права'
        ],
    ];

    


    public function safeUp()
    {
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'value' => $setting['default_value_old'],
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'description' => $setting['description_new']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
            ]);
        }
    }

    


    public function safeDown()
    {
        foreach (static::$settings_to_change as $setting) {
            
            $this->update('{{%text_settings}}', [
                'value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'value' => $setting['default_value_new']
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'default_value' => $setting['default_value_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
                'application_type' => TextSetting::APPLICATION_TYPE_DEFAULT
            ]);
            
            
            $this->update('{{%text_settings}}', [
                'description' => $setting['description_old']
            ], [
                'language' => $setting['language'],
                'name' => $setting['name'],
                'order' => $setting['order'],
            ]);
        }
    }
}
