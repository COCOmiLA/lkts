<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;
use yii\helpers\VarDumper;




class m220707_080538_rename_edu_default_doc_code extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->updateCodeSetting('edu_defaultdoc_guid', [
            'name' => 'edu_certificate_doc_type_guid',
            'description' => 'Код типа документа "Аттестат"',
        ]);
    }

    


    public function safeDown()
    {
        $this->updateCodeSetting('edu_certificate_doc_type_guid', [
            'name' => 'edu_defaultdoc_guid',
            'description' => 'Код типа документа об образовании по умолчанию',
        ]);
    }


    protected function updateCodeSetting(string $oldName, array $newParams)
    {
        $model = CodeSetting::findOne(['name' => $oldName]);

        if ($model === null) {
            $model = CodeSetting::findOne(['name' => $newParams['name']]);

            
            if ($model === null) {
                $model = new CodeSetting();
            }
        }

        $model->setAttributes($newParams);

        if ($model->validate(['name', 'description'])) {
            if (!$model->save(false)) {
                Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($newParams), 'CODE_SETTINGS');
            }
        } else {
            Yii::error("Ошибка валидации кода: " . VarDumper::dumpAsString($model->getErrorSummary(true)), 'CODE_SETTINGS');
        }
    }
}
