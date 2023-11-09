<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;




















class CgetEntranceTest extends \yii\db\ActiveRecord
{
    use ScenarioWithoutExistValidationTrait;

    


    public static function tableName()
    {
        return '{{%cget_entrance_test}}';
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
                    'priority',
                    'min_score',
                    'subject_ref_id',
                    'entrance_test_result_source_ref_id',
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
                ['entrance_test_result_source_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineFormReferenceType::class,
                'targetAttribute' => ['entrance_test_result_source_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['cget_entrance_test_set_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CgetEntranceTestSet::class,
                'targetAttribute' => ['cget_entrance_test_set_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['subject_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineReferenceType::class,
                'targetAttribute' => ['subject_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cget_entrance_test_set_id' => 'Cget Entrance Test Set ID',
            'priority' => 'Priority',
            'min_score' => 'Min Score',
            'subject_ref_id' => 'Subject Ref ID',
            'entrance_test_result_source_ref_id' => 'Entrance Test Result Source Ref ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    


    public function getRawCgetChildSubjects(): ActiveQuery
    {
        return $this->hasMany(CgetChildSubject::class, ['cget_entrance_test_id' => 'id']);
    }

    






    public function getCgetChildSubjects(bool $needSorted = true)
    {
        $tnCgetChildSubject = CgetChildSubject::tableName();
        $query = $this->getRawCgetChildSubjects();
        if ($needSorted) {
            $query = $query->orderBy("{$tnCgetChildSubject}.child_subject_index");
        }
        $query = $query->andWhere(["{$tnCgetChildSubject}.archive" => false]);
        return $query;
    }

    




    public function getCgetEntranceTestSet()
    {
        return $this->hasOne(CgetEntranceTestSet::class, ['id' => 'cget_entrance_test_set_id']);
    }

    




    public function getEntranceTestResultSourceRef()
    {
        return $this->hasOne(StoredDisciplineFormReferenceType::class, ['id' => 'entrance_test_result_source_ref_id']);
    }

    




    public function getSubjectRef()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'subject_ref_id']);
    }
}
