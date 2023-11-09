<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\settings\CodeSetting;




class m210427_052747_add_code_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $disciplineForm = '';
        $discipline = StoredDisciplineFormReferenceType::find()
            ->where(['reference_name' => 'ЕГЭ'])
            ->andWhere(['archive' => false])
            ->one();
        if ($discipline != null) {
            $disciplineForm = $discipline->id;
        }
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Код типа вступительного испытания "ЕГЭ"',
            'name' => 'discipline_ege_form',
            'value' => $disciplineForm
        ];
        if ($code->validate()) {
            $code->save();
        }

        $disciplineForm = '';
        $discipline = StoredDisciplineFormReferenceType::find()
            ->where(['reference_name' => 'Экз'])
            ->andWhere(['archive' => false])
            ->one();
        if ($discipline != null) {
            $disciplineForm = $discipline->id;
        }
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Код типа вступительного испытания "Экзамен"',
            'name' => 'discipline_exam_form',
            'value' => $disciplineForm
        ];
        if ($code->validate()) {
            $code->save();
        }
    }

    


    public function down()
    {
        $code = CodeSetting::findOne([
            'name' => 'discipline_ege_form',
        ]);
        if ($code != null) {
            $code->delete();
        }
        $code = CodeSetting::findOne([
            'name' => 'discipline_exam_form',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
