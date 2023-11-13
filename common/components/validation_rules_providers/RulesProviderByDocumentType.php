<?php

namespace common\components\validation_rules_providers;

use common\models\dictionary\DocumentType;
use common\models\dictionary\DocumentTypePropertiesSetting;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
use common\models\settings\ApplicationsSettings;
use common\modules\abiturient\validators\extenders\BaseValidationExtender;

class RulesProviderByDocumentType extends BaseValidationExtender
{
    const DocumentSeries = 'СерияДокумента';
    const DocumentNumber = 'НомерДокумента';
    const IssuedDate = 'ДатаВыдачи';
    const SubdivisionCode = 'КодПодразделения';
    const DateOfEnd = 'ДатаОкончания';
    const Additional = 'Дополнительно';
    const IssuedBy = 'КемВыдан';

    public function __construct(IHavePropsRelatedToDocumentType $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public static function getOneSSettingsMap(): array
    {
        return [
            'getDocumentSeriesPropertyName' => RulesProviderByDocumentType::DocumentSeries,
            'getDocumentNumberPropertyName' => RulesProviderByDocumentType::DocumentNumber,
            'getIssuedDatePropertyName' => RulesProviderByDocumentType::IssuedDate,
            'getSubdivisionCodePropertyName' => RulesProviderByDocumentType::SubdivisionCode,
            'getDateOfEndPropertyName' => RulesProviderByDocumentType::DateOfEnd,
            'getAdditionalPropertyName' => RulesProviderByDocumentType::Additional,
            'getIssuedByPropertyName' => RulesProviderByDocumentType::IssuedBy,
        ];
    }

    public function getRules(): array
    {
        if (static::isDisabled()) {
            return $this->model->ownRequiredRules();
        }
        $rules = [];
        $model = $this->model;
        foreach (static::getOneSSettingsMap() as $property_access_key => $one_s_value) {
            $property = $model->{$property_access_key}();
            if ($property) {
                $rules[] = [
                    $property,
                    'required',
                    'when' => function ($model) use ($one_s_value) {
                        
                        $documentType = $model->{$model::getDocumentTypePropertyName()};
                        if (!$documentType) {
                            return false;
                        }
                        [$required, $_] = DocumentTypePropertiesSetting::getPropertySetting($documentType, $one_s_value);
                        return $required;
                    },
                    'whenClient' => "function (attribute, value) {
                        return +$(attribute.input).attr('data-is_required') && !+$(attribute.input).attr('data-skip_validation');
                    }",
                ];
            }
        }
        return $rules;
    }

    private static ?bool $use_one_s_settings_for_fields_to_be_required = null;

    public static function isDisabled(): bool
    {
        if (static::$use_one_s_settings_for_fields_to_be_required === null) {
            static::$use_one_s_settings_for_fields_to_be_required = ApplicationsSettings::getValueByName('use_one_s_settings_for_fields_to_be_required') === '1';
        }
        return !static::$use_one_s_settings_for_fields_to_be_required;
    }
}