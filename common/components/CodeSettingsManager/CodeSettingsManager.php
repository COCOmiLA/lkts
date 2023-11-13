<?php


namespace common\components\CodeSettingsManager;

use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use common\components\CodeSettingsManager\exceptions\CodeNotFoundInConnectionArrayException;
use common\components\CodeSettingsManager\exceptions\EntityNotFoundByCodeException;
use common\models\dictionary\AdmissionBase;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Country;
use common\models\dictionary\DocumentType;
use common\models\dictionary\EducationType;
use common\models\dictionary\Gender;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\Rolerule;
use common\models\settings\CodeSetting;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\ApplicationType;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;






class CodeSettingsManager
{

    
    private const CODES_TO_ENTITY = [
        'identity_docs_guid' => DocumentType::class,
        'russian_passport_guid' => DocumentType::class,
        'edu_type_guid' => EducationType::class,
        'edu_certificate_doc_type_guid' => DocumentType::class,
        'bak_doc_guid' => DocumentType::class,
        'mag_doc_guid' => DocumentType::class,
        'spec_doc_guid' => DocumentType::class,
        'category_all' => AdmissionCategory::class,
        'category_specific_law' => AdmissionCategory::class,
        'target_reception_document_type_guid' => DocumentType::class,
        'full_cost_recovery_guid' => AdmissionBase::class,
        'special_quota_detail_group_guid' => StoredDetailGroupReferenceType::class,
        'target_reception_guid' => AdmissionBase::class,
        'budget_basis_guid' => AdmissionBase::class,
        'full_time_education_form_guid' => StoredEducationFormReferenceType::class,
        'male_guid' => Gender::class,
        'female_guid' => Gender::class,
        'agreement_document_type_guid' => DocumentType::class,
        'paid_contract_document_type' => DocumentType::class,
        'citizenship_guid' => Country::class,
        'russia_guid' => Country::class,
        'discipline_ege_form' => StoredDisciplineFormReferenceType::class,
        'discipline_exam_form' => StoredDisciplineFormReferenceType::class,
        'centralized_testing_doc_type_guid' => DocumentType::class,
        'belarusian_citizenship_guid' => Country::class,
        'application_document_type_guid' => DocumentType::class,
        'foreign_passport_guid' => DocumentType::class,
        'contractor_type_ufms_guid' => StoredContractorTypeReferenceType::class,
        'contractor_type_edu_guid' => StoredContractorTypeReferenceType::class,
        'chosen_campaign_id_by_default' => ApplicationType::class,
        'contractor_type_pref_guid' => StoredContractorTypeReferenceType::class,
        'contractor_type_olymp_guid' => StoredContractorTypeReferenceType::class,
        'contractor_type_target_reception_guid' => StoredContractorTypeReferenceType::class,
        'contractor_type_ia_guid' => StoredContractorTypeReferenceType::class,
        'not_verified_status_document_checker' => StoredDocumentCheckStatusReferenceType::class,
        'enrollment_rejection_doc_type_guid' => DocumentType::class,
    ];

    
    private const CODES_TO_QUERY = [
        'identity_docs_guid' => ['and', ['archive' => false], ['is_folder' => true], ['has_deletion_mark' => false]],
        'russian_passport_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'edu_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'edu_certificate_doc_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'bak_doc_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'mag_doc_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'spec_doc_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'category_all' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'category_specific_law' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'target_reception_document_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'full_cost_recovery_guid' => ['and', ['archive' => false]],
        'special_quota_detail_group_guid' => ['and', ['archive' => false]],
        'target_reception_guid' => ['and', ['archive' => false]],
        'budget_basis_guid' => ['and', ['archive' => false]],
        'full_time_education_form_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'male_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'female_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'agreement_document_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false], ['predefined_data_name' => AdmissionAgreement::DOCUMENT_TYPE_PREDEFINED_DATA_NAME]],
        'paid_contract_document_type' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'citizenship_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'russia_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'discipline_ege_form' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'discipline_exam_form' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'centralized_testing_doc_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'belarusian_citizenship_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
        'application_document_type_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'foreign_passport_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_ufms_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_edu_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_pref_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_olymp_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_target_reception_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'contractor_type_ia_guid' => ['and', ['archive' => false], ['has_deletion_mark' => false]],
        'chosen_campaign_id_by_default' => ['archive' => false],
        'not_verified_status_document_checker' => ['AND', ['archive' => false], ['has_deletion_mark' => false]],
        'enrollment_rejection_doc_type_guid' => ['and', ['archive' => false], ['is_folder' => false], ['has_deletion_mark' => false]],
    ];

    private const ENTITY_INPUT_MAPPING = [
        DocumentType::class => [
            'value' => 'ref_key',
            'text' => 'description'
        ],
        EducationType::class => [
            'value' => 'ref_key',
            'text' => 'description'
        ],
        AdmissionCategory::class => [
            'value' => 'ref_key',
            'text' => 'description'
        ],
        AdmissionBase::class => [
            'value' => 'ref_key',
            'text' => 'description'
        ],
        StoredEducationFormReferenceType::class => [
            'value' => 'reference_uid',
            'text' => 'reference_name'
        ],
        StoredDetailGroupReferenceType::class => [
            'value' => 'reference_uid',
            'text' => 'reference_name'
        ],
        Gender::class => [
            'value' => 'ref_key',
            'text' => 'description'
        ],
        Country::class => [
            'value' => 'ref_key',
            'text' => 'name'
        ],
        StoredDisciplineFormReferenceType::class => [
            'value' => 'reference_uid',
            'text' => 'reference_name'
        ],
        StoredContractorTypeReferenceType::class => [
            'value' => 'reference_uid',
            'text' => 'reference_name'
        ],
        ApplicationType::class => [
            'value' => 'id',
            'text' => 'name'
        ],
        StoredDocumentCheckStatusReferenceType::class => [
            'value' => 'id',
            'text' => 'humanReadableName'
        ],
    ];

    
    private static $disabledCodes = [
        'category_olympiad',
        'app_sending_type',
        'allow_print_application_with_any_status',
    ];

    
    private static $allowEmptyCodes = [
        'edu_type_guid',
        'chosen_campaign_id_by_default',
        'target_reception_document_type_guid',
        'belarusian_citizenship_guid',
        'centralized_testing_doc_type_guid',
        'special_quota_detail_group_guid',
        'contractor_type_pref_guid',
        'contractor_type_olymp_guid',
        'contractor_type_target_reception_guid',
        'contractor_type_ia_guid',
    ];

    






    public static function CheckCodeInArrayCodesToEntity($code, $throwError = true)
    {
        $status = in_array($code, array_keys(self::CODES_TO_ENTITY));
        if ($throwError && !$status) {
            throw new CodeNotFoundInConnectionArrayException($code, 'CODES_TO_ENTITY');
        }

        return $status;
    }

    




    private static function CheckCodeInArrayCodesToQuery($code)
    {
        if (!in_array($code, array_keys(self::CODES_TO_QUERY))) {
            throw new CodeNotFoundInConnectionArrayException($code, 'ENTITY_TO_QUERY');
        }
    }

    




    private static function CheckCodeInArrayEntityInputMapping($class)
    {
        if (!in_array($class, array_keys(self::ENTITY_INPUT_MAPPING))) {
            throw new CodeNotFoundInConnectionArrayException($class, 'ENTITY_INPUT_MAPPING');
        }
    }

    





    private static function GetEntityClassByCode(string $code)
    {
        self::CheckCodeInArrayCodesToEntity($code);
        return self::CODES_TO_ENTITY[$code] ?? null;
    }

    




    private static function GetQueryByCode(string $code)
    {
        return self::CODES_TO_QUERY[$code] ?? null;
    }

    





    private static function GetEntityMappingFieldsByEntity(string $entity)
    {
        self::CheckCodeInArrayEntityInputMapping($entity);
        return self::ENTITY_INPUT_MAPPING[$entity] ?? null;
    }

    






    public static function GetCodeEntityClassQuery(string $code): ActiveQuery
    {
        $class = self::GetEntityClassByCode($code);

        self::CheckCodeInArrayCodesToQuery($code);

        return $class::find()->where(self::GetQueryByCode($code));
    }

    





    public static function GetMappedCodeEntityArray(string $code): array
    {
        $query = self::GetCodeEntityClassQuery($code);
        $class = self::GetEntityClassByCode($code);

        $models = $query->all();
        self::CheckCodeInArrayEntityInputMapping($class);
        $fieldsToUse = self::GetEntityMappingFieldsByEntity($class);

        return ArrayHelper::map($models, $fieldsToUse['value'], $fieldsToUse['text']);
    }

    






    public static function GetEntityByCode(string $code)
    {
        $class = self::GetEntityClassByCode($code);
        $fieldsToUse = self::GetEntityMappingFieldsByEntity($class);
        $codeValue = \Yii::$app->configurationManager->getCode($code);

        $entity = self::GetCodeEntityClassQuery($code)
            ->andWhere([
                $fieldsToUse['value'] => $codeValue,
            ])
            ->one();

        if (is_null($entity) && !\Yii::$app->configurationManager->isEmptyCodeErrorsSuspended()) {
            $codeName = self::GetCodeEntity($code)->description;
            $table = $class::tableName();
            $field = $fieldsToUse['value'];
            throw new EntityNotFoundByCodeException($codeName, $table, $field, $codeValue);
        }
        return $entity;
    }

    





    private static function GetCodeEntity($code): CodeSetting
    {
        return \Yii::$app->configurationManager->getCodeEntity($code);
    }

    


    public static function GetDisabledCodes(): array
    {
        return static::$disabledCodes;
    }

    


    public static function GetAllowEmptyCodes(): array
    {
        return static::$allowEmptyCodes;
    }

    


    public static function NeedToFillCodes(): bool
    {
        if (defined('PORTAL_CONSOLE_INSTALLATION')) {
            return false;
        }
        
        $ruleRules = Rolerule::find()->limit(1)->one();
        if (!$ruleRules || !$ruleRules->abiturient) {
            return false;
        }

        $notInList = array_merge(
            static::GetDisabledCodes(),
            static::GetAllowEmptyCodes()
        );
        return CodeSetting::find()
            ->where(['value' => ['', null]])
            ->andWhere(['not in', 'name', $notInList])
            ->exists();
    }

    


    public static function EnsureRequiredCodesAreFilled()
    {
        if (static::NeedToFillCodes()) {
            throw new CodeNotFilledException("В портале не выполнены обязательные настройки. Обратитесь к администратору.");
        }
    }

    public static function getCodesToEntityList(): array
    {
        return static::CODES_TO_ENTITY;
    }

    public static function isRequired(CodeSetting $code): bool
    {
        return !in_array($code->name, static::GetAllowEmptyCodes());
    }
}
