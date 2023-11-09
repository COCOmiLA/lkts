<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\settings\CodeSetting;






class m220113_132020_update_discipline_form_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = CodeSetting::find()->andWhere(['name' => 'discipline_ege_form'])->one();
        if ($setting) {
            $reference = StoredDisciplineFormReferenceType::findOne((int)$setting->value);
            if ($reference) {
                $setting->value = $reference->reference_uid;
                if (!$setting->save(true, ['value'])) {
                    \Yii::error('Не удалось обновить код по-умолчнию discipline_ege_form');
                }
            }
        }
        
        if ($setting) {
            $setting = CodeSetting::find()->andWhere(['name' => 'discipline_exam_form'])->one();
            $reference = StoredDisciplineFormReferenceType::findOne((int)$setting->value);
            if ($reference) {
                $setting->value = $reference->reference_uid;
                if (!$setting->save(true, ['value'])) {
                    \Yii::error('Не удалось обновить код по-умолчнию discipline_exam_form');
                }
            }
        }
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::find()->andWhere(['name' => 'discipline_ege_form'])->one();
        if ($setting) {
            $setting->value = '';
            $setting->save(true, ['value']);
        }
        
        $setting = CodeSetting::find()->andWhere(['name' => 'discipline_exam_form'])->one();
        if ($setting) {
            $setting->value = '';
            $setting->save(true, ['value']);
        }
    }
}
