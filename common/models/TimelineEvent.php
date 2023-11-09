<?php

namespace common\models;

use common\models\query\TimelineEventQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class TimelineEvent extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%timeline_event}}';
    }

    


    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null
            ]
        ];
    }

    


    public static function find()
    {
        return new TimelineEventQuery(get_called_class());
    }

    


    public function rules()
    {
        return [
            [['application', 'category', 'event'], 'required'],
            [['data'], 'safe'],
            [['application', 'category', 'event'], 'string', 'max' => 64]
        ];
    }

    


    public function afterFind()
    {
        $this->data = json_decode((string)$this->data, true);
        parent::afterFind();
    }

    


    public function getFullEventName()
    {
        return sprintf('%s.%s', $this->category, $this->event);
    }
}
