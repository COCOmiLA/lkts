<?php

use common\components\LikeQueryManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\settings\CodeSetting;
use yii\helpers\ArrayHelper;




class m210722_093215_recover_new_code_setting_russian_passport_guid_value extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

        $codeSettingNew = CodeSetting::findOne([
            'name' => 'russian_passport_guid',
        ]);
        if (is_null($codeSettingNew)) {
            $codeSettingNew = new CodeSetting();
            $codeSettingNew->attributes = [
                'description' => 'Код документа, удостоверяющего личность по умолчанию (Паспорт РФ)',
                'name' => 'russian_passport_guid',
                'value' => ''
            ];
            $codeSettingNew->save();
        }

        $codeSettingOld = CodeSetting::findOne([
            'name' => 'passport_code',
        ]);
        $oldValue = ArrayHelper::getValue($codeSettingOld, 'value');
        $oldValueDocumentType = DocumentType::find()->where(['code' => $oldValue, 'archive' => false]);

        $queryAdditionalRussiaPassport = DocumentType::find()->where([LikeQueryManager::getActionName(), 'description', '%паспорт%',])->andWhere(['archive' => false]);
        $queryRussiaPassport = DocumentType::find()->where(['description' => 'Паспорт РФ', 'archive' => false]);

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

            if ($probablyDocumentType->description === 'Паспорт РФ') {
                $this->saveNewValueToCodeSetting($codeSettingNew, $probablyDocumentType->ref_key);
                return true;
            }
        } else {
            
            $probablyDocumentType = $probablyDocumentTypeQ->andWhere(['description' => 'Паспорт РФ'])->one();

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
        echo "m210722_093215_recover_new_code_setting_russian_passport_guid_value cannot be reverted.\n";

        return false;
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
