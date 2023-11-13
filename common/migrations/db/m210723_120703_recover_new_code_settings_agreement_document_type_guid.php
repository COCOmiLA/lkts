<?php

use common\components\LikeQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\settings\CodeSetting;
use yii\helpers\ArrayHelper;




class m210723_120703_recover_new_code_settings_agreement_document_type_guid extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $codeSettingNew = CodeSetting::findOne([
            'name' => 'agreement_document_type_guid',
        ]);
        if (is_null($codeSettingNew)) {
            $codeSettingNew = new CodeSetting();
            $codeSettingNew->attributes = [
                'description' => 'Код документа согласия на зачисления',
                'name' => 'agreement_document_type_guid',
                'value' => ''
            ];
            $codeSettingNew->save();
        }

        $codeSettingOld = CodeSetting::findOne([
            'name' => 'agreement_document_type',
        ]);
        $oldValue = ArrayHelper::getValue($codeSettingOld, 'value');
        $oldValueDocumentType = DocumentType::find()->where(['code' => $oldValue, 'archive' => false]);

        $queryAdditionalRussiaPassport = DocumentType::find()->where([LikeQueryManager::getActionName(), 'description', '%согласие%зачисление%',])->andWhere(['archive' => false]);
        $queryRussiaPassport = DocumentType::find()->where(['description' => 'Согласие на зачисление', 'archive' => false]);

        $passport = null;
        if (!$oldValueDocumentType->exists()) {

            if ($queryRussiaPassport->exists()) {
                $passport = $queryRussiaPassport->one();
                $this->saveNewValueToCodeSetting($codeSettingNew, $passport->ref_key);
                return true;
            }

            if ($queryAdditionalRussiaPassport->exists()) {
                $passport = $queryAdditionalRussiaPassport->one();
                $this->saveNewValueToCodeSetting($codeSettingNew, $passport->ref_key);
                return true;
            }
        }

        $probablyDocumentTypeQ = clone $oldValueDocumentType;

        if ((int)$probablyDocumentTypeQ->count() === 1) {
            
            $probablyDocumentType = $probablyDocumentTypeQ->one();

            if ($probablyDocumentType->description === 'Согласие на зачисление') {
                $this->saveNewValueToCodeSetting($codeSettingNew, $probablyDocumentType->ref_key);
                return true;
            }
        } else {
            
            $probablyDocumentType = $probablyDocumentTypeQ->andWhere(['description' => 'Согласие на зачисление'])->one();

            if (!is_null($probablyDocumentType)) {
                $this->saveNewValueToCodeSetting($codeSettingNew, $probablyDocumentType->ref_key);
                return true;
            }
        }


        if ($queryRussiaPassport->exists()) {
            $passport = $queryRussiaPassport->one();
            $this->saveNewValueToCodeSetting($codeSettingNew, $passport->ref_key);
            return true;
        }

        if ($queryAdditionalRussiaPassport->exists()) {
            $passport = $queryAdditionalRussiaPassport->one();
            $this->saveNewValueToCodeSetting($codeSettingNew, $passport->ref_key);
            return true;
        }
    }

    


    public function safeDown()
    {
        return true;
    }

    private function saveNewValueToCodeSetting(CodeSetting $codeSetting, string $value): void
    {
        $codeSetting->value = $value;
        $status = $codeSetting->save(true, ['value']);
        if (!$status) {
            $errors = $codeSetting->errors;
            Yii::error("Ошибка сохранения кода по умолчанию '{$codeSetting->name}'\n\n" . print_r($errors ?? [], true), 'RECOVER_CODE_SETTINGS_ERROR');
        }
    }
}
