<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\dictionary\EducationType;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredConditionTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;




















class CgetConditionType extends ActiveRecord
{
    private const JUNCTION_LIST_FIELD_CLASS = [
        'dictionary_education_type_id' => EducationType::class,
        'profile_reference_type_id' => StoredProfileReferenceType::class,
        'privilege_id' => Privilege::class,
        'special_mark_id' => SpecialMark::class,
    ];

    


    public static function tableName()
    {
        return '{{%cget_condition_type}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'cget_entrance_test_set_id',
                    'condition_type_reference_type_id'
                ],
                'required'
            ],
            [
                [
                    'cget_entrance_test_set_id',
                    'condition_type_reference_type_id',
                    'dictionary_education_type_id',
                    'profile_reference_type_id',
                    'updated_at',
                    'created_at'
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'cget_entrance_test_set_id',
                    'condition_type_reference_type_id',
                    'dictionary_education_type_id',
                    'profile_reference_type_id',
                    'updated_at',
                    'created_at'
                ],
                'integer'
            ],
            [
                ['archive'],
                'boolean'
            ],
            [
                ['cget_entrance_test_set_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CgetEntranceTestSet::class,
                'targetAttribute' => ['cget_entrance_test_set_id' => 'id']
            ],
            [
                ['condition_type_reference_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredConditionTypeReferenceType::class,
                'targetAttribute' => ['condition_type_reference_type_id' => 'id']
            ],
            [
                ['dictionary_education_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => EducationType::class,
                'targetAttribute' => ['dictionary_education_type_id' => 'id']
            ],
            [
                ['profile_reference_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredProfileReferenceType::class,
                'targetAttribute' => ['profile_reference_type_id' => 'id']
            ],
            [
                ['privilege_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Privilege::class,
                'targetAttribute' => ['privilege_id' => 'id']
            ],
            [
                ['special_mark_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => SpecialMark::class,
                'targetAttribute' => ['special_mark_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getCgetEntranceTestSet()
    {
        return $this->hasOne(CgetEntranceTestSet::class, ['id' => 'cget_entrance_test_set_id']);
    }

    




    public function getConditionTypeReferenceType()
    {
        return $this->hasOne(StoredConditionTypeReferenceType::class, ['id' => 'condition_type_reference_type_id']);
    }

    




    public function getDictionaryEducationType()
    {
        return $this->hasOne(EducationType::class, ['id' => 'dictionary_education_type_id']);
    }

    




    public function getProfileReferenceType()
    {
        return $this->hasOne(StoredProfileReferenceType::class, ['id' => 'profile_reference_type_id']);
    }

    




    public function getPrivilege()
    {
        return $this->hasOne(Privilege::class, ['id' => 'privilege_id']);
    }

    




    public function getSpecialMark()
    {
        return $this->hasOne(SpecialMark::class, ['id' => 'special_mark_id']);
    }

    


    public static function getJunctionListIdAndClass(): array
    {
        return static::JUNCTION_LIST_FIELD_CLASS;
    }

    


    public static function getJunctionListRefClassNameAndClass(): array
    {
        $result = [];
        foreach (static::JUNCTION_LIST_FIELD_CLASS as $class) {
            $refClassName = $class::getReferenceClassToFill();

            $result[$refClassName] = $class;
        }

        return $result;
    }

    




    public static function buildAttributesForFiltering(string $filterRow): array
    {
        $class = ArrayHelper::getValue(static::JUNCTION_LIST_FIELD_CLASS, $filterRow);
        if (!$class) {
            return [];
        }

        return [
            'filterJoin' => lcfirst(
                Inflector::id2camel(
                    str_replace(
                        '_id',
                        '',
                        $filterRow
                    ),
                    '_'
                )
            ),
            'filterTableName' => $class::tableName(),
            'referenceUidField' => $class::getUidColumnName(),
        ];
    }
}
