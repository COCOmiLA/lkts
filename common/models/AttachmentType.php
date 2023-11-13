<?php

namespace common\models;

use common\components\PageRelationManager;
use common\components\queries\AttachmentQuery;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;



























class AttachmentType extends ModelLinkedToReferenceType
{
    protected static $refKeyColumnName = null;

    protected static $refColumns = [
        'admission_campaign_ref_id' => 'CampaignRef',
        'document_set_ref_id' => 'DocumentSetRef',
        'document_type_id' => 'DocumentTypeRef',
    ];

    protected static $refAdditionalClasses = [
        'admission_campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'document_set_ref_id' => StoredDocumentSetReferenceType::class,
        'document_type_id' => DocumentType::class,
    ];

    public const RELATED_ENTITY_QUESTIONARY = PageRelationManager::RELATED_ENTITY_QUESTIONARY;
    public const RELATED_ENTITY_EGE = PageRelationManager::RELATED_ENTITY_EGE;
    public const RELATED_ENTITY_APPLICATION = PageRelationManager::RELATED_ENTITY_APPLICATION;
    public const RELATED_ENTITY_EDUCATION = PageRelationManager::RELATED_ENTITY_EDUCATION;
    public const RELATED_ENTITY_REGISTRATION = PageRelationManager::RELATED_ENTITY_REGISTRATION;
    public const RELATED_ENTITY_OLYMPIAD = PageRelationManager::RELATED_ENTITY_OLYMPIAD;
    public const RELATED_ENTITY_PREFERENCE = PageRelationManager::RELATED_ENTITY_PREFERENCE;
    public const RELATED_ENTITY_TARGET_RECEPTION = PageRelationManager::RELATED_ENTITY_TARGET_RECEPTION;

    public const SYSTEM_TYPE_COMMON = 0;
    public const SYSTEM_TYPE_TARGET = 1;
    public const SYSTEM_TYPE_PREFERENCE = 2;
    public const SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT = 3;
    public const SYSTEM_TYPE_ABITURIENT_AVATAR = 4;
    public const SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY = 5;
    public const SYSTEM_TYPE_IDENTITY_DOCUMENT = 6;
    public const SYSTEM_TYPE_EDUCATION_DOCUMENT = 7;
    public const SYSTEM_TYPE_APPLICATION_RETURN = 8;

    public const SYSTEM_TYPE_ENROLLMENT_REJECTION = 9;

    public static function tableName()
    {
        return '{{%attachment_type}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            [
                [
                    'custom_order',
                    'system_type'
                ],
                'integer'
            ],
            [
                'required',
                'boolean'
            ],
            [
                [
                    'name',
                    'related_entity'
                ],
                'required'
            ],
            [
                [
                    'name',
                    'tooltip_description'
                ],
                'string',
                'max' => 1000
            ],
            [
                'document_type_guid',
                'string',
                'max' => 255
            ],
            [
                [
                    'document_type_guid'
                ],
                'required',
                'when' => function ($model) {
                    return $model->system_type === self::SYSTEM_TYPE_COMMON;
                }
            ],
            [
                'related_entity',
                'validateRelatedEntity'
            ],
            [
                'hidden',
                'validateCanBeHided'
            ],
            [
                [
                    'allow_delete_file_after_app_approve',
                    'allow_add_new_file_after_app_approve',
                    'need_one_of_documents',
                ],
                'boolean'
            ],
            [
                [
                    'allow_delete_file_after_app_approve',
                    'allow_add_new_file_after_app_approve',
                    'need_one_of_documents',
                ],
                'default',
                'value' => false
            ],
            [
                [
                    'from1c',
                    'is_using',
                    'hidden'
                ],
                'boolean'
            ],
            [
                ['is_using'],
                'default',
                'value' => true
            ],
            [
                ['from1c'],
                'default',
                'value' => false
            ],
            [
                ['hidden'],
                'default',
                'value' => false
            ],
            [
                ['system_type'],
                'default',
                'value' => self::SYSTEM_TYPE_COMMON
            ],
            [
                [
                    'related_entity',
                    'campaign_code',
                    'document_set_code'
                ],
                'string',
                'max' => 100
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'name' => Yii::t('abiturient/attachment-type', 'Подпись для поля "name" формы "Тип скан документа": `Описание документа`'),
            'hidden' => Yii::t('abiturient/attachment-type', 'Подпись для поля "hidden" формы "Тип скан документа": `Скрытый`'),
            'required' => Yii::t('abiturient/attachment-type', 'Подпись для поля "required" формы "Тип скан документа": `Обязательный`'),
            'custom_order' => Yii::t('abiturient/attachment-type', 'Подпись для поля "custom_order" формы "Тип скан документа": `поле для сортировки`'),
            'document_type' => Yii::t('abiturient/attachment-type', 'Подпись для поля "document_type" формы "Тип скан документа": `Тип документа в 1С`'),
            'related_entity' => Yii::t('abiturient/attachment-type', 'Подпись для поля "related_entity" формы "Тип скан документа": `Связанная сущность`'),
            'document_type_guid' => Yii::t('abiturient/attachment-type', 'Подпись для поля "document_type_guid" формы "Тип скан документа": `Тип документа в 1С`'),
            'tooltip_description' => Yii::t('abiturient/attachment-type', 'Подпись для поля "tooltip_description" формы "Тип скан документа": `Текст всплывающей подсказки`'),
            'allow_delete_file_after_app_approve' => Yii::t('abiturient/attachment-type', 'Подпись для поля "allow_delete_file_after_app_approve" формы "Тип скан документа": `Разрешить удаление файла при наличии одобренного заявления`'),
            'allow_add_new_file_after_app_approve' => Yii::t('abiturient/attachment-type', 'Подпись для поля "allow_add_new_file_after_app_approve" формы "Тип скан документа": `Разрешить добавление файла при наличии одобренного заявления`'),
        ];
    }

    public static function GetRelatedList(bool $only_application_relations = false)
    {
        return PageRelationManager::GetRelatedList($only_application_relations);
    }

    public function validateRelatedEntity()
    {
        if ($this->from1c && !in_array($this->related_entity, array_keys(static::GetRelatedList(true)))) {
            $this->addError('related_entity', 'Невозможно указать данную связанную сущность для типа скан-копии из 1С');
        }
    }

    public function validateCanBeHided()
    {
        
        if (!$this->hidden || $this->oldAttributes['hidden']) {
            return;
        }
        $linked_attachments_count = $this->getAttachments()->count();
        if ($linked_attachments_count) {
            $this->addError('hidden', "Невозможно скрыть данный тип скан-копии так как к ней уже приложены документы: {$linked_attachments_count} шт.");
        }
    }

    public static function GetNotExistingAttachmentTypesToAdd($attachments, $campaign_ref_uid = null, $type = null)
    {
        $existing_attachments = $attachments;
        $used_type_ids = [];
        foreach ($existing_attachments as $ex_at) {
            $used_type_ids[] = $ex_at->attachment_type_id;
        }
        $types = self::GetCommonAttachmentTypesQuery($type)
            ->andWhere(['not', ['at.id' => $used_type_ids]]);
        if ($campaign_ref_uid !== null) {
            $typesFrom1C = self::GetCampaignAttachmentTypes($campaign_ref_uid, $type)
                ->andWhere(['not', ['at.id' => $used_type_ids]]);
            $types->union($typesFrom1C);
        }
        return $types->all();
    }

    public static function GetAttachmentTypesToAdd($type)
    {
        return AttachmentType::find()
            ->from('attachment_type at')
            ->where(['related_entity' => $type])
            ->andWhere(['hidden' => false])
            ->andWhere(['is_using' => true])
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    public static function GetRequiredCommonAttachmentTypeIds($type = null, $campaign_ref_uid = null): array
    {
        $types = self::GetCommonAttachmentTypesQuery($type)
            ->select('at.id')
            ->andWhere(['required' => true]);

        if ($campaign_ref_uid !== null) {
            $typesFrom1C = self::GetCampaignAttachmentTypes($campaign_ref_uid, $type)
                ->select('at.id')
                ->andWhere(['required' => true]);
            $types->union($typesFrom1C);
        }

        return ArrayHelper::getColumn($types->all(), 'id');
    }

    public function GetRelatedTitle()
    {
        return PageRelationManager::GetRelatedTitle($this->related_entity);
    }

    public function getRequiredLabel()
    {
        return $this->translateBooleanValue($this->required);
    }

    public function getHiddenLabel()
    {
        return $this->translateBooleanValue($this->hidden);
    }

    public function getAllowDeleteFileLabel()
    {
        return $this->translateBooleanValue($this->allow_delete_file_after_app_approve);
    }

    public function getAllowAddNewFileLabel()
    {
        return $this->translateBooleanValue($this->allow_add_new_file_after_app_approve);
    }

    




    private function translateBooleanValue($value): string
    {
        if ($value) {
            return Yii::t(
                'abiturient/attachment-type',
                'Подпись установленного флага формы "Тип скан документа": `да`'
            );
        }

        return Yii::t(
            'abiturient/attachment-type',
            'Подпись отсутствия установленного флага формы "Тип скан документа": `нет`'
        );
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['attachment_type_id' => 'id']);
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            return !$this->attachments;
        }
        return false;
    }

    


    public static function find()
    {
        return new AttachmentQuery(get_called_class());
    }

    public function hide()
    {
        $this->hidden = true;
        return $this->save(true, ['hidden']);
    }

    public static function GetRawAttachmentTypes($related_entity): ActiveQuery
    {
        $types = AttachmentType::find()
            ->from('attachment_type at')
            ->andWhere(['at.is_using' => true])
            ->andWhere(['at.hidden' => false])
            ->andWhere(['at.system_type' => self::SYSTEM_TYPE_COMMON])
            ->notInRegulation();
        if ($related_entity === null) {
            $types->andWhere(['not in', 'at.related_entity', [self::RELATED_ENTITY_QUESTIONARY, self::RELATED_ENTITY_REGISTRATION]]);
        } else {
            $types->andWhere(['at.related_entity' => $related_entity]);
        }
        return $types;
    }

    public static function GetCommonAttachmentTypesQuery($related_entity = null): ActiveQuery
    {
        return self::GetRawAttachmentTypes($related_entity)
            ->joinWith('admissionCampaignRef', false)
            ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => null]);
    }

    public static function GetCampaignAttachmentTypes($campaignReferenceUid, $related_entity = null): ActiveQuery
    {
        return self::GetRawAttachmentTypes($related_entity)
            ->joinWith('admissionCampaignRef', false)
            ->andWhere(['at.from1c' => true])
            ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => $campaignReferenceUid]);
    }

    public static function GetUnionAttachmentTypes($campaignReferenceUid, $related_entity = null): ActiveQuery
    {
        $types = self::GetCommonAttachmentTypesQuery($related_entity);
        if ($campaignReferenceUid !== null) {
            $typesFrom1C = self::GetCampaignAttachmentTypes($campaignReferenceUid, $related_entity);
            $types->union($typesFrom1C);
        }
        return $types;
    }

    


    public static function GetSystemTypes(): array
    {
        return [
            self::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT,
            self::SYSTEM_TYPE_PREFERENCE,
            self::SYSTEM_TYPE_TARGET,
            self::SYSTEM_TYPE_ABITURIENT_AVATAR,
            self::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY,
            self::SYSTEM_TYPE_IDENTITY_DOCUMENT,
            self::SYSTEM_TYPE_EDUCATION_DOCUMENT,
            self::SYSTEM_TYPE_APPLICATION_RETURN,
            self::SYSTEM_TYPE_ENROLLMENT_REJECTION,
        ];
    }

    public function inQuestionary()
    {
        return in_array($this->related_entity, [
            AttachmentType::RELATED_ENTITY_QUESTIONARY,
            AttachmentType::RELATED_ENTITY_REGISTRATION,
        ], true);
    }

    


    public function getAdmissionCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'admission_campaign_ref_id']);
    }

    


    public function getDocumentSetRef()
    {
        return $this->hasOne(StoredDocumentSetReferenceType::class, ['id' => 'document_set_ref_id']);
    }

    


    public function getDocumentType()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }

    public function getRegulation()
    {
        return $this->hasOne(Regulation::class, ['attachment_type' => 'id']);
    }

    


    public function getAttachmentTypeTemplate()
    {
        return $this->hasOne(AttachmentTypeTemplate::class, ['attachment_type_id' => 'id']);
    }

    public function getOrBuildAttachmentTypeTemplate(): AttachmentTypeTemplate
    {
        $attachmentTypeTemplate = $this->getAttachmentTypeTemplate()->one();

        if (!$attachmentTypeTemplate) {
            $attachmentTypeTemplate = new AttachmentTypeTemplate();
            $attachmentTypeTemplate->attachment_type_id = $this->id;
        }

        return $attachmentTypeTemplate;
    }
}
