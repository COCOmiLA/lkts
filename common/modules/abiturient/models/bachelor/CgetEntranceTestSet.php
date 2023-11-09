<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\EducationType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubjectSetReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

























class CgetEntranceTestSet extends ActiveRecord
{
    use ScenarioWithoutExistValidationTrait;

    


    public static function tableName()
    {
        return '{{%cget_entrance_test_set}}';
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
                    'dictionary_competitive_group_entrance_test_id',
                    'education_type_ref_id',
                    'entrance_test_set_ref_id',
                    'updated_at',
                    'created_at',
                    'profile_ref_id',
                ],
                'integer'
            ],
            [
                [
                    'archive',
                ],
                'boolean'
            ],
            [
                ['education_type_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => EducationType::class,
                'targetAttribute' => ['education_type_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['entrance_test_set_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredSubjectSetReferenceType::class,
                'targetAttribute' => ['entrance_test_set_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['dictionary_competitive_group_entrance_test_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DictionaryCompetitiveGroupEntranceTest::class,
                'targetAttribute' => ['dictionary_competitive_group_entrance_test_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['profile_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredProfileReferenceType::class,
                'targetAttribute' => ['profile_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dictionary_competitive_group_entrance_test_id' => 'Competitive Group Entrance Test ID',
            'education_type_ref_id' => 'Education Type Ref ID',
            'entrance_test_set_ref_id' => 'Entrance Test Set Ref ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    




    public function getCgetEntranceTests()
    {
        $tn = CgetEntranceTest::tableName();
        return $this->getRawEntranceTests()
            ->andWhere(["{$tn}.archive" => false])
            ->orderBy('cget_entrance_test.priority');
    }

    public function getRawEntranceTests()
    {
        return $this->hasMany(CgetEntranceTest::class, ['cget_entrance_test_set_id' => 'id']);
    }

    




    public function getCgetConditionTypes()
    {
        $tn = CgetConditionType::tableName();
        return $this->getRawConditionTypes()
            ->andWhere(["{$tn}.archive" => false]);
    }

    public function getRawConditionTypes()
    {
        return $this->hasMany(CgetConditionType::class, ['cget_entrance_test_set_id' => 'id']);
    }

    




    public function getCgetRequiredPreferences()
    {
        $tn = CgetRequiredPreference::tableName();
        return $this->getRawEntranceTests()
            ->andOnCondition(["{$tn}.archive" => false]);
    }

    public function getRawCgetRequiredPreferences()
    {
        return $this->hasMany(CgetRequiredPreference::class, ['cget_entrance_test_set_id' => 'id']);
    }

    




    public function getDictionaryCompetitiveGroupEntranceTest()
    {
        return $this->hasOne(DictionaryCompetitiveGroupEntranceTest::class, ['id' => 'dictionary_competitive_group_entrance_test_id']);
    }

    




    public function getEducationTypeRef()
    {
        return $this->hasOne(EducationType::class, ['id' => 'education_type_ref_id']);
    }

    




    public function getEntranceTestSetRef()
    {
        return $this->hasOne(StoredSubjectSetReferenceType::class, ['id' => 'entrance_test_set_ref_id']);
    }

    




    public function getProfileRef()
    {
        return $this->hasOne(StoredProfileReferenceType::class, ['id' => 'profile_ref_id']);
    }

    


    public function getAllowMultiplyAlternativeSubjects(): bool
    {
        return ArrayHelper::getValue($this, 'dictionaryCompetitiveGroupEntranceTest.allow_multiply_alternative_subjects', false);
    }
}
