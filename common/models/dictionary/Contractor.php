<?php

namespace common\models\dictionary;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\dictionary\StoredReferenceType\StoredContractorReferenceType;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\modules\abiturient\models\drafts\DraftsManager;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;



















class Contractor extends ActiveRecord implements IHavePropsRelatedToDocumentType, ICanGivePropsToCompare
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    public $need_subdivision_code = false;

    protected ?DocumentType $documentTypeVorValidation = null;

    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    


    public static function tableName()
    {
        return '{{%dictionary_contractor}}';
    }

    public function ownRequiredRules(): array
    {
        return [
            [
                ['subdivision_code'],
                'required',
                'when' => function ($model, $attribute) {
                    if (empty($model->documentType)) {
                        return false;
                    }
                    return ($model->documentType->ref_key == Yii::$app->configurationManager->getCode('russian_passport_guid'));
                },
                'whenClient' => 'function(attribute, value) {
                    var contractor_doc_type = $(attribute.input).closest(".document-root").find("[data-document_type_input]").val();                    
                    if (contractor_doc_type == ' . (CodeSettingsManager::GetEntityByCode('russian_passport_guid')->id ?? 0) . ') {
                        return !+$(attribute.input).attr("data-skip_validation");
                    }
                    return false;
                }'
            ]
        ];
    }

    public function baseRules()
    {
        return [
            [
                ['name'],
                'required',
                'whenClient' => 'function(attribute, value) {
                    return !+$(attribute.input).attr("data-skip_validation");
                }'
            ],
            [['contractor_ref_id', 'contractor_type_ref_id'], 'integer'],
            [['archive', 'location_not_found'], 'boolean'],
            [['name'], 'string', 'max' => 1000],
            [['location_code'], 'string', 'max' => 100],
            [['subdivision_code', 'status', 'location_name'], 'string', 'max' => 255],
            [['contractor_ref_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoredContractorReferenceType::class, 'targetAttribute' => ['contractor_ref_id' => 'id']],
            [['contractor_type_ref_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoredContractorTypeReferenceType::class, 'targetAttribute' => ['contractor_type_ref_id' => 'id']],
            [['status'], 'default', 'value' => static::STATUS_PENDING],
            [['archive'], 'default', 'value' => 0],
        ];
    }

    public function rules()
    {
        return [...$this->_document_type_validation_extender->getRules(), ...static::baseRules()];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "id" формы "Контрагент": `ИД`'),
            'name' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "name" формы "Контрагент": `Наименование`'),
            'subdivision_code' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "subdivision_code" формы "Контрагент": `Код подразделения`'),
            'contractor_ref_id' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "contractor_ref_id" формы "Контрагент": `Контрагент`'),
            'contractor_type_ref_id' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "contractor_type_ref_id" формы "Контрагент": `Тип контрагента`'),
            'status' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "status" формы "Контрагент": `Статус`'),
            'archive' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "archive" формы "Контрагент": `Архив`'),
            'location_code' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "location_code" формы "Контрагент": `Город/Нас. пункт`'),
            'location_name' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "location_name" формы "Контрагент": `Город/Нас. пункт`'),
            'location_not_found' => Yii::t('common/models/dictionary/contractor', 'Подпись для поля "location_not_found" формы "Контрагент": `Не нашёл адрес в адресном классификаторе`'),
        ];
    }

    


    public function getContractorRef()
    {
        return $this->hasOne(StoredContractorReferenceType::class, ['id' => 'contractor_ref_id']);
    }

    


    public function getContractorTypeRef()
    {
        return $this->hasOne(StoredContractorTypeReferenceType::class, ['id' => 'contractor_type_ref_id']);
    }

    public static function linkToApproved(string $model_class, string $relation_name, Contractor $approved_contractor)
    {
        $models = $model_class::find()
            ->joinWith(["{$relation_name} contractor" => function(ActiveQuery $q) use ($approved_contractor) {
                $q = ContractorManager::buildCompareConditions($q, [
                    'name' => $approved_contractor->name,                    
                    'contractor_type_reference_uid' => $approved_contractor->contractorTypeRef->reference_uid ?? null,
                    'subdivision_code' => $approved_contractor->subdivision_code,
                    'location_code' => $approved_contractor->location_code,
                    'location_name' => $approved_contractor->location_name,
                ]);                
            }], false)
            ->andWhere(['contractor.status' => Contractor::STATUS_PENDING])
            ->andWhere(['!=', 'contractor.id', $approved_contractor->id]);

        foreach ($models->each(1000) as $model) {
            DraftsManager::SuspendHistory($model);
            $model->link($relation_name, $approved_contractor);
        }
    }

    public static function getSubdivisionCodePropertyName(): string
    {
        return 'subdivision_code';
    }

    public static function getIssuedDatePropertyName(): string
    {
        return '';
    }

    public static function getDateOfEndPropertyName(): string
    {
        return '';
    }

    public static function getAdditionalPropertyName(): string
    {
        return '';
    }

    public static function getIssuedByPropertyName(): string
    {
        return '';
    }

    public static function getDocumentSeriesPropertyName(): string
    {
        return '';
    }

    public static function getDocumentNumberPropertyName(): string
    {
        return '';
    }

    public static function getDocumentTypePropertyName(): string
    {
        return 'documentType';
    }

    public function getDocumentType()
    {
        return $this->documentTypeVorValidation;
    }

    public function setDocumentTypeForValidation(DocumentType $documentType)
    {
        $this->documentTypeVorValidation = $documentType;
    }

    public function getLocation(): ActiveQuery
    {
        return $this->hasOne(Fias::class, ['code' => 'location_code']);
    }

    public function getFullname(): string
    {
        $parts = [$this->name];

        if ($this->location) {
            $parts[] = $this->location->fullname;
        } elseif ($this->location_name) {
            $parts[] = $this->location_name;
        }

        if ($this->subdivision_code) {
            $parts[] = $this->subdivision_code;
        }

        return implode(', ', $parts);
    }

    public function beforeValidate()
    {
        $this->cleanUnusedAttributes();
        return parent::beforeValidate();
    }

    public function cleanUnusedAttributes()
    {
        if ($this->location_not_found) {
            $this->location_code = null;
        } else {
            $this->location_name = null;
        }
    }

    public function getPropsToCompare(): array
    {
        return [
            'name',
            'contractor_type_ref_id',
            'subdivision_code',
            'location_code',
            'location_name',
        ];
    }
}
