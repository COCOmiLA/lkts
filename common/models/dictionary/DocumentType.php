<?php

namespace common\models\dictionary;

use common\components\queries\DictionaryQuery;
use common\models\AttachmentType;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\IndividualAchievementDocumentType;
use common\models\interfaces\IArchiveQueryable;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IReferencesOData;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;








class DocumentType extends ModelFrom1CByOData implements IReferencesOData, IRestorableReferenceDictionary, IArchiveQueryable, IFillableReferenceDictionary
{
    protected static $referenceClassName = 'Справочник.ТипыДокументов';

    


    public static function tableName()
    {
        return '{{%dictionary_document_type}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['ref_key', 'data_version', 'code', 'description'], 'required'],
            [['ref_key', 'parent_key'], 'string', 'max' => 255],
            [['code', 'description', 'formula'], 'string', 'max' => 1000],
            [['data_version'], 'string', 'max' => 100],
            [['ref_key', 'data_version'], 'unique', 'targetAttribute' => ['ref_key', 'data_version']]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'ref_key' => 'Id 1C',
            'data_version' => 'версия',
            'code' => 'ключ',
            'description' => 'описание',
            'formula' => 'формула',
            'parent_key' => 'родительский ключ 1С',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(DocumentType::class, ['ref_key' => 'parent_key']);
    }

    public function getChildren()
    {
        return $this->hasMany(DocumentType::class, ['parent_key' => 'ref_key']);
    }

    public function getDatacode()
    {
        return [
            'data-code' => $this->code,
        ];
    }

    




    static function getByCode($code)
    {
        return DocumentType::find()
            ->active()
            ->where(['code' => $code])
            ->limit(1)
            ->one();
    }

    public static function find()
    {
        return new DictionaryQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public static function updateLinks()
    {
        $all_items = DocumentType::find()
            ->active()
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {
                BachelorPreferences::updateAll(['document_type_id' => ArrayHelper::getValue($item, 'id')], [
                    'bachelor_preferences.archive' => false,
                    'bachelor_preferences.document_type' => $item->{DocumentType::$codeColumnName},
                    'document_type_id' => null
                ]);
                BachelorTargetReception::updateAll(['document_type_id' => ArrayHelper::getValue($item, 'id')], [
                    'bachelor_target_reception.archive' => false,
                    'bachelor_target_reception.document_type' => $item->{DocumentType::$codeColumnName},
                    'document_type_id' => null
                ]);
                AttachmentType::updateAll(['document_type_id' => ArrayHelper::getValue($item, 'id')], [
                    'attachment_type.is_using' => true,
                    'attachment_type.document_type' => $item->{DocumentType::$codeColumnName},
                    'document_type_id' => null
                ]);
                IndividualAchievementDocumentType::updateAll(['document_type_ref_id' => ArrayHelper::getValue($item, 'id')], [
                    'individual_achievements_document_types.archive' => false,
                    'individual_achievements_document_types.document_type' => $item->{DocumentType::$codeColumnName},
                    'document_type_ref_id' => null
                ]);
            }
        }
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            BachelorPreferences::class,
            'document_type_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            BachelorTargetReception::class,
            'document_type_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            AttachmentType::class,
            'document_type_id',
            ['is_using' => true]
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();

        (new BaseRestoreHandler(
            $this,
            IndividualAchievementDocumentType::class,
            'document_type_ref_id'
        ))
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }

    public static function getReferenceClassToFill(): string
    {
        return static::getReferenceClassName();
    }

    public function fillDictionary()
    {
    }

    





    public static function processArchiveDocForDropdown(string $attr, $modelDocumentTypeAttrValue)
    {
        $documentTypesOptions = [];
        $documentType = DocumentType::find()
            ->andWhere([$attr => $modelDocumentTypeAttrValue])
            ->one();
        if (!$documentType) {
            return [
                'description' => '',
                'documentTypesOptions' => $documentTypesOptions,
            ];
        }

        if ($documentType->archive) {
            $documentTypesOptions[$modelDocumentTypeAttrValue] = ['class' => 'text-white bg-danger'];
        }

        return [
            'description' => $documentType->description,
            'documentTypesOptions' => $documentTypesOptions,
        ];
    }
}
