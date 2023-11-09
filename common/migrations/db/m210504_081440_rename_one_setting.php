<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210504_081440_rename_one_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $codeSetting = CodeSetting::findOne([
            'name' => 'target_reception_document_type'
        ]);

        if(!is_null($codeSetting)) {
            $codeSetting->description = 'Тип документа, доступный для выбора поступающим при вводе данных о  целевом договоре';
            if($codeSetting->validate()) {
                $codeSetting->save(false);
            } else {
                \Yii::error("Ошибка при сохранении сущности кода по умолчанию.\n" . print_r($codeSetting->errors, true));
                return false;
            }
        }
    }

    


    public function safeDown()
    {
        $codeSetting = CodeSetting::findOne([
            'name' => 'target_reception_document_type'
        ]);

        if(!is_null($codeSetting)) {
            $codeSetting->description = 'Код типа документа по умолчанию для подтверждающего документа целевого приема';
            if($codeSetting->validate()) {
                $codeSetting->save(false);
            } else {
                \Yii::error("Ошибка при сохранении сущности кода по умолчанию.\n" . print_r($codeSetting->errors, true));
                return false;
            }
        }
    }

    













}
