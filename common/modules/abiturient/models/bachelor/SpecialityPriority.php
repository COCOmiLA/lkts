<?php

namespace common\modules\abiturient\models\bachelor;

use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;










class SpecialityPriority extends \yii\db\ActiveRecord implements IHaveIdentityProp, ICanGivePropsToCompare
{
    public static function tableName()
    {
        return '{{%speciality_priorities}}';
    }

    public function rules()
    {
        return [
            [['bachelor_speciality_id', 'enrollment_priority', 'inner_priority', 'priority_group_identifier'], 'required',],
            [['bachelor_speciality_id', 'enrollment_priority', 'inner_priority'], 'integer',],
            [['priority_group_identifier'], 'string',],

            [['bachelor_speciality_id'], 'exist', 'skipOnError' => true, 'targetClass' => BachelorSpeciality::class, 'targetAttribute' => ['bachelor_speciality_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'enrollment_priority' => 'Приоритет зачисления',
            'inner_priority' => 'Внутренний приоритет',
            'priority_group_identifier' => 'Идентификатор приоритетной группы',
        ];
    }

    public function getBachelorSpeciality()
    {
        return $this->hasOne(BachelorSpeciality::class, ['id' => 'bachelor_speciality_id']);
    }

    public function getIdentityString(): string
    {
        return $this->priority_group_identifier . '-' . $this->enrollment_priority . '-' . $this->inner_priority;
    }

    public function getPropsToCompare(): array
    {
        return [
            'enrollment_priority',
        ];
    }

}
