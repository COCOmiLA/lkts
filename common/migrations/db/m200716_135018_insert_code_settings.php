<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\AdmissionBase;
use common\models\settings\CodeSetting;




class m200716_135018_insert_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $docType = AdmissionBase::findOne(['description' => 'Полное возмещение затрат', 'archive' => false]);
        $code = new CodeSetting();
        $code->description = 'Код основания поступления полное возмещение затрат';
        $code->name = 'full_cost_recovery_code';

        if ($docType != null) {
            $code->value = $docType->code;
        } else {
            $code->value = '';
        }
        if ($code->validate()) {
            $code->save();
        }

        $docType = AdmissionBase::findOne(['description' => 'Целевой прием', 'archive' => false]);
        $code = new CodeSetting();
        $code->description = 'Код основания поступления целевой прием';
        $code->name = 'target_reception_code';

        if ($docType != null) {
            $code->value = $docType->code;
        } else {
            $code->value = '';
        }
        $code->save();

        $docType = AdmissionBase::findOne(['description' => 'Бюджетная основа', 'archive' => false]);
        $code = new CodeSetting();
        $code->description = 'Код основания поступления бюджетная основа';
        $code->name = 'budget_basis_code';

        if ($docType != null) {
            $code->value = $docType->code;
        } else {
            $code->value = '';
        }
        if ($code->validate()) {
            $code->save();
        }
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'full_cost_recovery_code',
        ]);
        if ($code != null) {
            $code->delete();
        }

        $code = CodeSetting::findOne([
            'name' => 'target_reception_code',
        ]);
        if ($code != null) {
            $code->delete();
        }

        $code = CodeSetting::findOne([
            'name' => 'budget_basis_code',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
