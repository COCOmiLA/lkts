<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
















class CgetRequiredPreference extends \yii\db\ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%cget_required_preference}}';
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
                    'created_at',
                    'updated_at',
                    'dictionary_privileges_id',
                    'cget_entrance_test_set_id',
                    'dictionary_special_marks_id',
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'created_at',
                    'updated_at',
                    'dictionary_privileges_id',
                    'cget_entrance_test_set_id',
                    'dictionary_special_marks_id',
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
                ['dictionary_privileges_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Privilege::class,
                'targetAttribute' => ['dictionary_privileges_id' => 'id']
            ],
            [
                ['dictionary_special_marks_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => SpecialMark::class,
                'targetAttribute' => ['dictionary_special_marks_id' => 'id']
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

    




    public function getPrivilege()
    {
        return $this->hasOne(Privilege::class, ['id' => 'dictionary_privileges_id']);
    }

    




    public function getSpecialMark()
    {
        return $this->hasOne(SpecialMark::class, ['id' => 'dictionary_special_marks_id']);
    }
}
