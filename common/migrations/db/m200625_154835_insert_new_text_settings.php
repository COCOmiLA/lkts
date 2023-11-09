<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\TextSetting;




class m200625_154835_insert_new_text_settings extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $benefitSpec = new TextSetting();
        $benefitSpec->attributes = [
            'name' => 'benefits_before_spec',
            'description' => 'Текст перед "Имеются отличительные признаки для поступления"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_BENEFITS
        ];
        $benefitSpec->save(false);
        $benefitOlymp = new TextSetting();
        $benefitOlymp->attributes = [
            'name' => 'benefits_before_olymp',
            'description' => 'Текст перед "Имеется право на поступление без вступительных испытаний"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_BENEFITS
        ];
        $benefitOlymp->save(false);
        $benefitTarget = new TextSetting();
        $benefitTarget->attributes = [
            'name' => 'benefits_before_target',
            'description' => 'Текст перед "По квоте целевого приема"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_BENEFITS
        ];
        $benefitTarget->save(false);
        $loadScans = new TextSetting();
        $loadScans->attributes = [
            'name' => 'load_scans',
            'description' => 'Текст сверху "Сканы документов"',
            'value' => '',
            'order' => 0,
            'category' => TextSetting::CATEGORY_SCANS
        ];
        $loadScans->save(false);
    }

    


    public function down()
    {
        $settings_to_delete = [
            [
                'name' => 'benefits_before_spec',
                'category' => TextSetting::CATEGORY_BENEFITS
            ],
            [
                'name' => 'benefits_before_olymp',
                'category' => TextSetting::CATEGORY_BENEFITS
            ],
            [
                'name' => 'benefits_before_target',
                'category' => TextSetting::CATEGORY_BENEFITS
            ],
            [
                'name' =>'load_scans',
                'category' => TextSetting::CATEGORY_SCANS
            ]
        ];
        foreach ($settings_to_delete as $setting) {
            $to_delete = TextSetting::findOne($setting);
            if($to_delete != null) {
                $to_delete->delete();
            }
        }
    }
}
