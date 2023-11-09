<?php

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\ModelFrom1CByOData;
use common\models\settings\CodeSetting;
use yii\db\Query;
use yii\helpers\VarDumper;




class m220111_114441_add_new_reference_code_settings extends MigrationWithDefaultOptions
{
    protected static $toReplace = [
        'paid_contract_document_type' => [
            'name' => 'paid_contract_document_type',
            'description' => 'Код документа договора об оказании платных образовательных услуг',
            'value' => ''
        ],
        'citizenship_code' => [
            'name' => 'citizenship_guid',
            'description' => 'Код гражданства по умолчанию (РФ)',
            'value' => ''
        ],
        'russia_code' => [
            'name' => 'russia_guid',
            'description' => 'Код страны по умолчанию (Россия)',
            'value' => ''
        ],
    ];
    
    


    public function safeUp()
    {
        $codesToEntity = CodeSettingsManager::getCodesToEntityList();
        
        foreach (static::$toReplace as $oldName => $newParams) {
            if (isset($codesToEntity[$newParams['name']])) {
                $class = $codesToEntity[$newParams['name']];
                $model = null;
                
                if (is_subclass_of($class, ModelFrom1CByOData::class)) {
                    try {
                        
                        $model = $class::findByCode(\Yii::$app->configurationManager->getCode($oldName));
                        if ($model) {
                            $uid_column = $model::getUidColumnName();
                            $newParams['value'] = $model->{$uid_column};
                        }
                    } catch (\Throwable $e) {
                        \Yii::error("Не удалось определить UID кода по умолчанию {$newParams['name']}: " . $e->getMessage());
                    }
                }
                
                
                if (is_subclass_of($class, StoredReferenceType::class)) {
                    try {
                        
                        $model = $class::findOne([
                            'archive' => false,
                            'reference_id' => \Yii::$app->configurationManager->getCode($oldName)
                        ]);
                        if ($model) {
                            $newParams['value'] = $model->reference_uid;
                        }
                    } catch (\Throwable $e) {
                        \Yii::error("Не удалось определить UID кода по умолчанию {$newParams['name']}: " . $e->getMessage());
                    }
                }
            }
            
            $this->updateCodeSetting($oldName, $newParams);
        }
    }

    


    public function safeDown()
    {
        foreach (static::$toReplace as $oldName => $newParams) {
            $this->restoreCodeSetting($oldName, $newParams);
        }
    }
    
    protected function updateCodeSetting(string $oldName, array $newParams)
    {
        $model = CodeSetting::findOne(['name' => $oldName]);
        
        if ($model === null) {
            $model = CodeSetting::findOne(['name' => $newParams['name']]);
            
            
            if ($model === null) {
                $model = new CodeSetting();
            }
            
            $newParams['value'] = $model->value;
        }
        
        if ($newParams['value'] === null) {
            $newParams['value'] = '';
        }

        $model->setAttributes($newParams);

        if (!$model->save(true, ['name', 'description', 'value'])) {
            Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($newParams), 'CODE_SETTINGS');
        }
    }
    
    protected function restoreCodeSetting(string $oldName, array $newParams)
    {
        $model = CodeSetting::findOne(['name' => $newParams['name']]);
        
        if ($model === null) {
            $model = CodeSetting::findOne(['name' => $oldName]);
        }
        
        $oldParams = (new Query)
            ->select('*')
            ->from('{{%code_settings_archive}}')
            ->where(['name' => $oldName])
            ->one();
        
        $model->name = $oldName;
        $model->description = $oldParams['description'] ?? $newParams['description'];
        $model->value = $oldParams['value'] ?? '';
        
        if (!$model->save(true, ['name', 'description', 'value'])) {
            Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($newParams), 'CODE_SETTINGS');
        }
    }
}
