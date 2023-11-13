<?php

namespace common\models\dictionary;


use common\models\dictionary\StoredReferenceType\StoredOlympicClassReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicKindReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicTypeReferenceType;
use common\models\EmptyCheck;
use common\models\interfaces\IReferencesOData;
use common\models\ModelLinkedToReferenceType;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

















class Olympiad extends ModelLinkedToReferenceType implements IReferencesOData
{
    protected static $refKeyColumnName = 'ref_id';

    protected static $refClass = StoredOlympicReferenceType::class;


    protected static $refColumns = [
        'ref_id' => 'OlympicRef',
        'olympic_type_ref_id' => 'OlympicTypeRef',
        'olympic_level_ref_id' => 'OlympicLevelRef',
        'olympic_kind_ref_id' => 'OlympicKindRef',
        'olympic_class_ref_id' => 'OlympicClassRef',
        'olympic_profile_ref_id' => 'OlympicProfileRef',
    ];

    protected static $refAdditionalClasses = [
        'ref_id' => StoredOlympicReferenceType::class,
        'olympic_type_ref_id' => StoredOlympicTypeReferenceType::class,
        'olympic_level_ref_id' => StoredOlympicLevelReferenceType::class,
        'olympic_kind_ref_id' => StoredOlympicKindReferenceType::class,
        'olympic_class_ref_id' => StoredOlympicClassReferenceType::class,
        'olympic_profile_ref_id' => StoredOlympicProfileReferenceType::class,
    ];

    


    public static function tableName()
    {
        return '{{%dictionary_olympiads}}';
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
            [
                [
                    'code',
                    'type',
                    'place',
                    'level',
                    'class',
                    'education_class',
                    'kind',
                    'profile',
                    'need_ege',
                    'name',
                    'year',
                    'ref_id',
                    'olympic_type_ref_id',
                    'olympic_level_ref_id',
                    'olympic_kind_ref_id',
                    'olympic_class_ref_id',
                    'olympic_profile_ref_id',
                ],
                'safe'
            ],
            [[
                'ref_id',
                'olympic_type_ref_id',
                'olympic_level_ref_id',
                'olympic_kind_ref_id',
                'olympic_class_ref_id',
                'olympic_profile_ref_id',
            ], 'integer'],
            [['ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicReferenceType::class, 'targetAttribute' => ['ref_id' => 'id']],
            [['olympic_type_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicTypeReferenceType::class, 'targetAttribute' => ['olympic_type_ref_id' => 'id']],
            [['olympic_level_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicLevelReferenceType::class, 'targetAttribute' => ['olympic_level_ref_id' => 'id']],
            [['olympic_kind_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicKindReferenceType::class, 'targetAttribute' => ['olympic_kind_ref_id' => 'id']],
            [['olympic_class_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicClassReferenceType::class, 'targetAttribute' => ['olympic_class_ref_id' => 'id']],
            [['olympic_profile_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredOlympicProfileReferenceType::class, 'targetAttribute' => ['olympic_profile_ref_id' => 'id']],
        ];
    }

    public static function updateLinks()
    {
        $all_items = Olympiad::find()
            ->where(['archive' => false])
            ->with('olympicRef')
            ->batch();
        foreach ($all_items as $items_batch) {
            foreach ($items_batch as $item) {
                $code = $item->olympicRef->reference_id;
                OlympiadFilter::updateAll(['olympiad_id' => ArrayHelper::getValue($item, 'id')], [
                    'dictionary_olympiads_filter.archive' => false,
                    'dictionary_olympiads_filter.olympiad_code' => $code,
                    'olympiad_id' => null
                ]);
                BachelorPreferences::updateAll(['olympiad_id' => ArrayHelper::getValue($item, 'id')], [
                    'bachelor_preferences.archive' => false,
                    'bachelor_preferences.olympiad_code' => $code,
                    'olympiad_id' => null
                ]);
            }
        }
    }

    public static function findByCode($code)
    {
        $code = (string)$code;
        if (EmptyCheck::isEmpty($code)) {
            return null;
        }
        $query = Olympiad::find()->joinWith('olympicRef')->where(['or', [
            'dictionary_olympiads.' . static::$codeColumnName => $code,
        ], [
            'olympic_reference_type.reference_id' => $code
        ]]);

        if (static::isArchivable()) {
            $query->andWhere([
                'dictionary_olympiads.' . static::$archiveColumnName => static::$archiveColumnNegativeValue
            ]);
        }
        return $query->limit(1)->one();
    }

    public function getOlympicRef()
    {
        return $this->hasOne(
            StoredOlympicReferenceType::class,
            ['id' => 'ref_id']
        );
    }

    public function getOlympicTypeRef()
    {
        return $this->hasOne(
            StoredOlympicTypeReferenceType::class,
            ['id' => 'olympic_type_ref_id']
        );
    }

    public function getOlympicLevelRef()
    {
        return $this->hasOne(
            StoredOlympicLevelReferenceType::class,
            ['id' => 'olympic_level_ref_id']
        );
    }

    public function getOlympicKindRef()
    {
        return $this->hasOne(
            StoredOlympicKindReferenceType::class,
            ['id' => 'olympic_kind_ref_id']
        );
    }

    public function getOlympicProfileRef()
    {
        return $this->hasOne(
            StoredOlympicProfileReferenceType::class,
            ['id' => 'olympic_profile_ref_id']
        );
    }

    public function getOlympicClassRef()
    {
        return $this->hasOne(
            StoredOlympicClassReferenceType::class,
            ['id' => 'olympic_class_ref_id']
        );
    }

    public function getOlympiadFilters()
    {
        return $this->hasMany(OlympiadFilter::class, ['olympiad_id' => 'id']);
    }

    public function getFullName(): string
    {
        $ref_name = $this->olympicRef->reference_name;
        $class = '';
        $profile = '';
        if ($this->olympicClassRef) {
            $class = $this->olympicClassRef->reference_name;
        }
        if ($this->olympicProfileRef) {
            $profile = "({$this->olympicProfileRef->reference_name})";
        }
        return trim("{$ref_name} {$class} {$profile}");
    }
}