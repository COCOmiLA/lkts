<?php

use common\components\migrations\traits\TableOptionsTrait;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\DocumentType;
use common\models\dictionary\EducationType;
use common\models\dictionary\Gender;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\ModelFrom1CByOData;
use common\models\settings\CodeSetting;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\VarDumper;




class m211022_160039_add_new_reference_code_settings extends Migration
{
    use TableOptionsTrait;
    
    protected static $toReplace = [
        'identity_docs_code' => [
            'name' => 'identity_docs_guid',
            'description' => 'Код категории "Документы, удостоверяющие личность (паспорта)"',
            'value' => ''
        ],
        'edu_type_code' => [
            'name' => 'edu_type_guid',
            'description' => 'Код уровня образования по умолчанию',
            'value' => ''
        ],
        'edu_defaultdoc_code' => [
            'name' => 'edu_defaultdoc_guid',
            'description' => 'Код типа документа об образовании по умолчанию',
            'value' => ''
        ],
        'bak_doc_code' => [
            'name' => 'bak_doc_guid', 
            'description' => 'Код диплома бакалавра',
            'value' => ''
        ],
        'mag_doc_code' => [
            'name' => 'mag_doc_guid',
            'description' => 'Код диплома магистра',
            'value' => ''
        ],
        'spec_doc_code' => [
            'name' => 'spec_doc_guid',
            'description' => 'Код диплома специалиста',
            'value' => ''
        ],
        'category_all' => [
            'name' => 'category_all',
            'description' => 'Код категории приема на общих основаниях',
            'value' => ''
        ],
        'category_specific_law' => [
            'name' => 'category_specific_law',
            'description' => 'Код категории приема абитуриентов имеющих особое право',
            'value' => ''
        ],
        'category_olympiad' => [
            'name' => 'category_olympiad',
            'description' => 'Код категории приема без вступительных испытаний',
            'value' => ''
        ],
        'target_reception_document_type' => [
            'name' => 'target_reception_document_type',
            'description' => 'Тип документа, доступный для выбора поступающим при вводе данных о целевом договоре',
            'value' => ''
        ],
        'full_cost_recovery_code' => [
            'name' => 'full_cost_recovery_guid',
            'description' => 'Код основания поступления полное возмещение затрат',
            'value' => ''
        ],
        'target_reception_code' => [
            'name' => 'target_reception_guid',
            'description' => 'Код основания поступления целевой прием',
            'value' => ''
        ],
        'budget_basis_code' => [
            'name' => 'budget_basis_guid',
            'description' => 'Код основания поступления бюджетная основа',
            'value' => ''
        ],
        'full_time_education_form_code' => [
            'name' => 'full_time_education_form_guid',
            'description' => 'Код очной формы обучения',
            'value' => ''
        ],
        'part_time_education_form_code' => [
            'name' => 'part_time_education_form_guid',
            'description' => 'Код очно-заочной формы обучения',
            'value' => ''
        ],
        'male_code' => [
            'name' => 'male_guid',
            'description' => 'Код мужского пола',
            'value' => ''
        ],
        'female_code' => [
            'name' => 'female_guid',
            'description' => 'Код женского пола',
            'value' => ''
        ],
    ];
    
    private const CODES_TO_ENTITY = [
        'identity_docs_guid' => DocumentType::class,
        'edu_type_guid' => EducationType::class,
        'edu_defaultdoc_guid' => DocumentType::class,
        'bak_doc_guid' => DocumentType::class,
        'mag_doc_guid' => DocumentType::class,
        'spec_doc_guid' => DocumentType::class,
        'category_all' => AdmissionCategory::class,
        'category_specific_law' => AdmissionCategory::class,
        'category_olympiad' => AdmissionCategory::class,
        'target_reception_document_type' => DocumentType::class,
        'full_cost_recovery_guid' => StoredEducationSourceReferenceType::class,
        'target_reception_guid' => StoredEducationSourceReferenceType::class,
        'budget_basis_guid' => StoredEducationSourceReferenceType::class,
        'full_time_education_form_guid' => StoredEducationFormReferenceType::class,
        'part_time_education_form_guid' => StoredEducationFormReferenceType::class,
        'male_guid' => Gender::class,
        'female_guid' => Gender::class,
    ];
    
    


    public function safeUp()
    {
        $this->backupCodeSettings();
        
        foreach (static::$toReplace as $oldName => $newParams) {
            if (isset(static::CODES_TO_ENTITY[$newParams['name']])) {
                $class = static::CODES_TO_ENTITY[$newParams['name']];
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
        
        if (!empty(Yii::$app->db->getTableSchema('{{%code_settings_archive}}'))) {
            $this->dropTable('{{%code_settings_archive}}');
        }
    }

    



    protected function backupCodeSettings()
    {
        if (!empty(Yii::$app->db->getTableSchema('{{%code_settings_archive}}'))) {
            return;
        }
        
        $this->createTable('{{%code_settings_archive}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),

        ], static::GetTableOptions());

        $oldCodes = (new Query)->select('*')->from('{{%code_settings}}')->all();

        foreach ($oldCodes as $code) {
            $this->insert('{{%code_settings_archive}}', [
                'name' => $code['name'],
                'description' => $code['description'],
                'value' => $code['value']
            ]);
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
        
        if ($model->validate(['name', 'description', 'value'])) {
            if (!$model->save(false)) {
                Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($newParams), 'CODE_SETTINGS');
            }
        } else {
            Yii::error("Ошибка валидации кода: " . VarDumper::dumpAsString($model->getErrorSummary(true)), 'CODE_SETTINGS');
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
        
        if ($model->validate(['name', 'description', 'value'])) {
            if (!$model->save(false)) {
                Yii::error("Ошибка при записи кода: " . VarDumper::dumpAsString($newParams), 'CODE_SETTINGS');
            }
        } else {
            Yii::error("Ошибка валидации кода: " . VarDumper::dumpAsString($model->getErrorSummary(true)), 'CODE_SETTINGS');
        }
    }
}
