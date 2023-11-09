<?php

namespace backend\models\applicationTypeHistory;

use yii\behaviors\TimestampBehavior;
use yii\bootstrap4\Html;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;














class ApplicationTypeHistoryEntityClassInput extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%application_type_history_entity_class_input}}';
    }

    


    public function behaviors()
    {
        return ['timestamp' => ['class' => TimestampBehavior::class]];
    }

    


    public function rules()
    {
        return [
            [
                ['application_type_history_id'],
                'required'
            ],
            [
                [
                    'application_type_history_id',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [
                [
                    'input_name',
                    'actual_value',
                    'old_value'
                ],
                'string',
                'max' => 255
            ],
            [
                ['application_type_history_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ApplicationTypeHistory::class,
                'targetAttribute' => ['application_type_history_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getApplicationTypeHistory()
    {
        return $this->hasOne(ApplicationTypeHistory::class, ['id' => 'application_type_history_id']);
    }

    


    public function renderHumanActualValue()
    {
        return $this->renderHumanValue('actual_value');
    }

    


    public function renderHumanOldValue()
    {
        return $this->renderHumanValue('old_value');
    }

    



    public function renderHumanValue(string $field)
    {
        if ((int)$this->{$field}) {
            return Html::tag('i', null, ['class' => 'fa fa-check-square-o']);
        }
        return Html::tag('i', null, ['class' => 'fa fa-square-o']);
    }
}
