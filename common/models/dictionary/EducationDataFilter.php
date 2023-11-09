<?php


namespace common\models\dictionary;


use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\ModelLinkedToReferenceType;















class EducationDataFilter extends ModelLinkedToReferenceType
{
    protected static $refColumns = [
        'education_level_id' => 'EducationLevelRef',
        'education_type_id' => 'EducationTypeRef',
        'document_type_id' => 'DocumentTypeRef',
    ];

    protected static $refAdditionalClasses = [
        'education_level_id' => StoredEducationLevelReferenceType::class,
        'education_type_id' => EducationType::class,
        'document_type_id' => DocumentType::class,
    ];


    public function rules()
    {
        return [
            [
                [
                    'education_level_id',
                    'education_type_id',
                    'document_type_id',
                ],
                'integer'
            ],
            [
                [
                    'actual',
                    'allow_profile_input'
                ],
                'boolean'
            ],
            [
                ['period'],
                'string'
            ],
            [
                ['education_level_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredEducationLevelReferenceType::class,
                'targetAttribute' => ['education_level_id' => 'id']
            ],
            [
                ['education_type_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => EducationType::class,
                'targetAttribute' => ['education_type_id' => 'id']
            ],
            [
                ['document_type_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => DocumentType::class,
                'targetAttribute' => ['document_type_id' => 'id']
            ],
        ];
    }

    


    public static function tableName()
    {
        return '{{%education_data_filters}}';
    }

    public function getEducationLevelRef()
    {
        return $this->hasOne(StoredEducationLevelReferenceType::class, ['id' => 'education_level_id']);
    }

    public function getEducationTypeRef()
    {
        return $this->hasOne(EducationType::class, ['id' => 'education_type_id']);
    }

    public function getDocumentTypeRef()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }
}
