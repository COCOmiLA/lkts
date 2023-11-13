<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use yii\behaviors\TimestampBehavior;















class CgetChildSubject extends \yii\db\ActiveRecord
{
    use ScenarioWithoutExistValidationTrait;

    


    public static function tableName()
    {
        return '{{%cget_child_subject}}';
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
                    'cget_entrance_test_id',
                    'child_subject_index',
                    'child_subject_ref_id',
                    'updated_at',
                    'created_at',
                ],
                'integer'
            ],
            [
                [
                    'archive'
                ],
                'boolean'
            ],
            [
                ['child_subject_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineReferenceType::class,
                'targetAttribute' => ['child_subject_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['cget_entrance_test_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CgetEntranceTest::class,
                'targetAttribute' => ['cget_entrance_test_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cget_entrance_test_id' => 'StoredCget Entrance Test ID',
            'child_subject_index' => 'Child Subject Index',
            'child_subject_ref_id' => 'Child Subject Ref ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    




    public function getCgetEntranceTest()
    {
        return $this->hasOne(CgetEntranceTest::class, ['id' => 'cget_entrance_test_id']);
    }

    




    public function getChildSubjectRef()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'child_subject_ref_id']);
    }

    




    public function getSubjectRef()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'child_subject_ref_id']);
    }
}
